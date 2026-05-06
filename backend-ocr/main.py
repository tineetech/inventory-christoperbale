"""
main.py — patched v2
Update:
  1. /job-status/{job_id}: trigger cleanup_pdf_task ketika status=done
  2. /scan-resi-multiple-async: pass pdf_path lewat enqueue_pdf_pages
     (sudah di-handle di tasks_fixed.py, tidak ada perubahan di sini)
  3. Import cleanup_pdf_task dari tasks
"""

from fastapi import FastAPI, UploadFile, File, Form
import shutil
import uuid
import os
import json
import redis

from ocr import extract_text
from ocr_pdf import extract_text_pdf
from parser.parser_shopee import parse_shopee
from parser.parser_tiktok import parse_tiktok
from ocr_tiktok import extract_tiktok_from_pdf, extract_tiktok_from_image
from ocr_shopee_multiple import extract_multiple_resi_from_pdf, extract_multiple_resi_from_image
from ocr_tiktok_multiple import (
    extract_multiple_resi_tiktok_from_pdf,
    extract_multiple_resi_tiktok_from_image,
)
from pdf2image import convert_from_bytes
import base64
import io

from tasks import enqueue_pdf_pages, cleanup_pdf_task
from celery_app import REDIS_URL

r = redis.from_url(REDIS_URL, decode_responses=True)

app = FastAPI()

UPLOAD_DIR = "uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)

ALLOWED_EXT = ["jpg", "jpeg", "png", "pdf"]


def _cleanup(path: str):
    try:
        if os.path.exists(path):
            os.remove(path)
    except Exception as e:
        print(f"[WARN cleanup] {e}")


# ──────────────────────────────────────────────────────────────────
# Endpoint sync (lama) — tetap ada
# ──────────────────────────────────────────────────────────────────

@app.post("/scan-resi")
async def scan_resi(mode: str = Form(...), file: UploadFile = File(...)):
    ext = file.filename.split(".")[-1].lower()
    if ext not in ALLOWED_EXT:
        return {"error": "Format file tidak didukung. Gunakan PDF/JPG/PNG"}

    filename = f"{UPLOAD_DIR}/{uuid.uuid4()}.{ext}"
    with open(filename, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    if ext == "pdf":
        if mode == "tiktok":
            text, items = extract_tiktok_from_pdf(filename)
        else:
            text, items = extract_text_pdf(filename)
    else:
        if mode == "tiktok":
            text, items = extract_tiktok_from_image(filename)
        else:
            text, items = extract_text(filename)

    if mode == "shopee":
        result = parse_shopee(text, items)
    elif mode == "tiktok":
        result = parse_tiktok(text, items)
    else:
        result = {"error": "mode tidak dikenali"}

    _cleanup(filename)
    return {"mode": mode, "result": result}


@app.post("/scan-resi-multiple")
async def scan_resi_multiple(mode: str = Form(...), file: UploadFile = File(...)):
    """Endpoint sync — untuk backward compatibility."""
    ext = file.filename.split(".")[-1].lower()
    if ext not in ALLOWED_EXT:
        return {"error": "Format file tidak didukung. Gunakan PDF/JPG/PNG"}

    filename = f"{UPLOAD_DIR}/{uuid.uuid4()}.{ext}"
    with open(filename, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    results = []
    try:
        if mode == "shopee":
            results = (extract_multiple_resi_from_pdf(filename) if ext == "pdf"
                       else extract_multiple_resi_from_image(filename))
        elif mode == "tiktok":
            results = (extract_multiple_resi_tiktok_from_pdf(filename) if ext == "pdf"
                       else extract_multiple_resi_tiktok_from_image(filename))
        else:
            _cleanup(filename)
            return {"error": f"mode '{mode}' belum didukung"}
    except Exception as e:
        _cleanup(filename)
        return {"error": str(e)}

    _cleanup(filename)

    valid_results = [r for r in results if r.get("resi") or r.get("order_id") or r.get("skus")]
    return {"mode": mode, "total": len(valid_results), "data": valid_results}


# ──────────────────────────────────────────────────────────────────
# ENDPOINT ASYNC: Submit job → return job_id langsung
# ──────────────────────────────────────────────────────────────────

@app.post("/scan-resi-multiple-async")
async def scan_resi_multiple_async(
    mode: str = Form(...),
    file: UploadFile = File(...)
):
    ext = file.filename.split(".")[-1].lower()
    if ext not in ALLOWED_EXT:
        return {"error": "Format file tidak didukung. Gunakan PDF/JPG/PNG"}

    if mode not in ("shopee", "tiktok"):
        return {"error": f"mode '{mode}' tidak dikenal"}

    job_id   = str(uuid.uuid4())
    filename = f"{UPLOAD_DIR}/{job_id}.{ext}"
    with open(filename, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    total_pages = None
    if ext == "pdf":
        try:
            from pypdf import PdfReader
            reader = PdfReader(filename)
            total_pages = len(reader.pages)
        except Exception:
            pass

    r.hset(f"ocr_job:{job_id}", mapping={
        "status"      : "queued",
        "mode"        : mode,
        "total_pages" : total_pages or 0,
        "done_pages"  : 0,
        "failed_pages": 0,
    })
    r.expire(f"ocr_job:{job_id}", 3600)

    if ext == "pdf":
        enqueue_pdf_pages.apply_async(
            args=[job_id, filename, mode],
            queue="ocr",
        )
    else:
        from tasks import process_page_task
        r.hset(f"ocr_job:{job_id}", "total_pages", 1)
        r.hset(f"ocr_job:{job_id}", "status", "processing")
        process_page_task.apply_async(
            args  = [job_id, 1, filename, mode],
            kwargs= {"pdf_path": None},
            queue = "ocr",
        )

    print(f"[Async] Job {job_id} queued | mode={mode} | ext={ext} | pages={total_pages}")

    return {
        "job_id"     : job_id,
        "status"     : "queued",
        "total_pages": total_pages,
    }


# ──────────────────────────────────────────────────────────────────
# ENDPOINT: Cek status + ambil hasil
# FIX v2: trigger PDF cleanup saat status=done
# ──────────────────────────────────────────────────────────────────

@app.get("/job-status/{job_id}")
async def job_status(job_id: str):
    meta = r.hgetall(f"ocr_job:{job_id}")
    if not meta:
        return {"error": "Job tidak ditemukan atau sudah expired"}

    total  = int(meta.get("total_pages", 0))
    done   = int(meta.get("done_pages", 0))
    failed = int(meta.get("failed_pages", 0))
    status = meta.get("status", "unknown")

    progress_pct = round((done + failed) / total * 100) if total > 0 else 0

    raw_pages = r.lrange(f"ocr_job:{job_id}:pages", 0, -1)
    pages = []
    for raw in raw_pages:
        try:
            pages.append(json.loads(raw))
        except Exception:
            pass

    pages.sort(key=lambda p: p.get("page", 999))

    # ── FIX v2: Trigger PDF cleanup saat pertama kali status=done ──
    # Flag "pdf_cleaned" di Redis supaya tidak trigger berkali-kali
    if status == "done" and not meta.get("pdf_cleaned"):
        pdf_path = meta.get("pdf_path")
        if pdf_path:
            cleanup_pdf_task.apply_async(args=[job_id], queue="ocr")
            r.hset(f"ocr_job:{job_id}", "pdf_cleaned", "1")
            print(f"[job_status] Triggered PDF cleanup untuk job {job_id}")

    return {
        "job_id"      : job_id,
        "status"      : status,
        "mode"        : meta.get("mode"),
        "total_pages" : total,
        "done_pages"  : done,
        "failed_pages": failed,
        "progress_pct": progress_pct,
        "data"        : pages,
        "error"       : meta.get("error"),
    }


@app.post("/convert-pdf")
async def convert_pdf(file: UploadFile = File(...)):
    contents = await file.read()
    images   = convert_from_bytes(contents)
    result   = []
    for img in images:
        buffered = io.BytesIO()
        img.save(buffered, format="JPEG")
        img_str = base64.b64encode(buffered.getvalue()).decode()
        result.append(img_str)
    return result