# tasks.py — patched v5
# Fix v5: merge halaman dengan order_id sama saat job selesai
# Merge dilakukan di Redis level setelah semua page task selesai.

from dotenv import load_dotenv
import json
import traceback
import tempfile
import os

import cv2
import redis

from celery_app import celery
from pdf2image import convert_from_path
from ocr_shopee_multiple import _process_single_page_cv, _cv_to_base64_jpeg
from ocr_tiktok_multiple import _process_single_page as _process_tiktok_page

load_dotenv()

REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")
r = redis.from_url(REDIS_URL, decode_responses=True)

JOB_TTL = 3600


def _job_key(job_id: str) -> str:
    return f"ocr_job:{job_id}"


def _init_job(job_id: str, total_pages: int, mode: str):
    r.hset(_job_key(job_id), mapping={
        "status"       : "processing",
        "mode"         : mode,
        "total_pages"  : total_pages,
        "done_pages"   : 0,
        "failed_pages" : 0,
    })
    r.expire(_job_key(job_id), JOB_TTL)
    r.delete(f"ocr_job:{job_id}:pages")


# ──────────────────────────────────────────────────────────────────
# FIX v5: Merge pages dengan order_id sama di Redis
# ──────────────────────────────────────────────────────────────────

def _merge_redis_pages(job_id: str):
    """
    Baca semua page result dari Redis, merge yang punya order_id sama,
    lalu tulis balik hasil yang sudah di-merge.

    Dipanggil hanya sekali saat job status berubah ke 'done'.

    Kasus yang di-handle:
    - Halaman 21: resi=JX9489617439, order_id=584175205435671665, items=[]
    - Halaman 22: resi=None, order_id=584175205435671665, items=[dalbz37]
    → Hasil merge: resi=JX9489617439, order_id=584175205435671665, items=[dalbz37]
    """
    pages_key = f"ocr_job:{job_id}:pages"
    raw_pages = r.lrange(pages_key, 0, -1)

    if not raw_pages:
        return

    # Parse semua hasil
    results = []
    for raw in raw_pages:
        try:
            results.append(json.loads(raw))
        except Exception:
            pass

    # Sort by page number supaya urutan konsisten
    results.sort(key=lambda x: x.get("page", 0))

    # Merge by order_id
    merged = []
    order_id_map = {}  # order_id -> index di merged

    for page_result in results:
        order_id = page_result.get("order_id")
        resi     = page_result.get("resi")
        items    = page_result.get("items", [])
        mode     = page_result.get("mode")  # shopee tidak perlu merge

        # Shopee tidak punya multi-halaman per order, skip merge
        # (bisa dideteksi dari absennya order_id atau mode)
        if not order_id:
            merged.append(page_result)
            continue

        if order_id in order_id_map:
            # Merge ke entri yang sudah ada
            idx      = order_id_map[order_id]
            existing = merged[idx]

            # Gabungkan items, hindari duplikat SKU
            existing_skus = {i["sku"] for i in existing.get("items", [])}
            new_items = []
            for item in items:
                if item.get("sku") and item["sku"] not in existing_skus:
                    new_items.append(item)
                    existing_skus.add(item["sku"])

            existing["items"] = existing.get("items", []) + new_items
            existing["skus"]  = [i["sku"] for i in existing["items"]]

            # Ambil resi jika halaman utama belum punya
            if not existing.get("resi") and resi:
                existing["resi"] = resi

            # Catat halaman yang terlibat (opsional, untuk debug)
            pages_involved = existing.get("pages", [existing.get("page")])
            if page_result.get("page") not in pages_involved:
                pages_involved.append(page_result["page"])
            existing["pages"] = pages_involved

            print(f"[Merge v5] Page {page_result.get('page')} merged → "
                  f"order_id={order_id} total_items={len(existing['items'])}")
        else:
            # Entri baru
            entry = dict(page_result)
            entry["pages"] = [page_result.get("page")]
            merged.append(entry)
            order_id_map[order_id] = len(merged) - 1

    # Tulis balik ke Redis (hapus lama, isi baru)
    pipe = r.pipeline()
    pipe.delete(pages_key)
    for entry in merged:
        pipe.rpush(pages_key, json.dumps(entry))
    pipe.expire(pages_key, JOB_TTL)
    pipe.execute()

    print(f"[Merge v5] Job {job_id}: {len(results)} halaman → "
          f"{len(merged)} resi unik setelah merge.")


# ──────────────────────────────────────────────────────────────────
# Helper Redis
# ──────────────────────────────────────────────────────────────────

def _check_and_finalize(job_id: str):
    """
    Cek apakah job sudah selesai (done+failed >= total).
    Jika ya, jalankan merge lalu set status=done.
    Pakai Redis SET NX sebagai lock supaya merge hanya jalan sekali
    meskipun 2 task selesai bersamaan.
    """
    meta  = r.hgetall(_job_key(job_id))
    done  = int(meta.get("done_pages", 0))
    failed= int(meta.get("failed_pages", 0))
    total = int(meta.get("total_pages", 0))

    if done + failed >= total and total > 0:
        # Lock: hanya 1 worker yang boleh merge
        lock_key = f"ocr_job:{job_id}:merge_lock"
        acquired = r.set(lock_key, "1", nx=True, ex=60)
        if acquired:
            mode = meta.get("mode", "")
            if mode == "tiktok":
                try:
                    _merge_redis_pages(job_id)
                except Exception as e:
                    print(f"[Merge v5] Error saat merge job {job_id}: {e}")
                    traceback.print_exc()
            r.hset(_job_key(job_id), "status", "done")
            print(f"[Job {job_id}] Status → done "
                  f"(done={done} failed={failed} total={total})")


def _save_page_result(job_id: str, page_num: int, result: dict):
    pipe = r.pipeline()
    pipe.rpush(f"ocr_job:{job_id}:pages", json.dumps(result))
    pipe.hincrby(_job_key(job_id), "done_pages", 1)
    pipe.expire(f"ocr_job:{job_id}:pages", JOB_TTL)
    pipe.execute()

    _check_and_finalize(job_id)  # FIX v5: ganti inline check → centralized + merge


def _mark_page_failed(job_id: str, page_num: int, error: str):
    pipe = r.pipeline()
    pipe.rpush(f"ocr_job:{job_id}:pages", json.dumps({
        "page"        : page_num,
        "resi"        : None,
        "order_id"    : None,
        "items"       : [],
        "skus"        : [],
        "image_base64": "",
        "error"       : error,
    }))
    pipe.hincrby(_job_key(job_id), "failed_pages", 1)
    pipe.expire(f"ocr_job:{job_id}:pages", JOB_TTL)
    pipe.execute()

    _check_and_finalize(job_id)  # FIX v5: sama


def _safe_remove(path: str):
    try:
        if path and os.path.exists(path):
            os.remove(path)
    except Exception as e:
        print(f"[Cleanup] Gagal hapus {path}: {e}")


# ──────────────────────────────────────────────────────────────────
# Task: proses 1 halaman (tidak berubah dari v4)
# ──────────────────────────────────────────────────────────────────

@celery.task(bind=True, max_retries=3, default_retry_delay=5)
def process_page_task(
    self,
    job_id        : str,
    page_num      : int,
    img_path      : str,
    mode          : str,
    pdf_path      : str = None,
    ekspedisi_mode: str = None,
):
    if not os.path.exists(img_path):
        raise Exception(f"File sudah tidak ada: {img_path}")

    try:
        img_cv = cv2.imread(img_path)
        if img_cv is None:
            raise ValueError(f"Gagal baca gambar: {img_path}")

        if mode == "shopee":
            result = _process_single_page_cv(
                img_cv,
                page_num,
                pdf_path=pdf_path,
                ekspedisi_mode=ekspedisi_mode,
            )
        elif mode == "tiktok":
            result = _process_tiktok_page(
                img_cv,
                page_num,
                pdf_path=pdf_path,
                ekspedisi_mode=ekspedisi_mode,
            )
        else:
            raise ValueError(f"mode tidak dikenal: {mode}")

        _save_page_result(job_id, page_num, result)
        _safe_remove(img_path)

    except Exception as exc:
        print(f"[Task] Page {page_num} error: {exc}")
        traceback.print_exc()
        try:
            raise self.retry(exc=exc, countdown=5)
        except self.MaxRetriesExceededError:
            _safe_remove(img_path)
            _mark_page_failed(job_id, page_num, str(exc))


# ──────────────────────────────────────────────────────────────────
# Task: entry point — terima PDF, split, queue tiap halaman
# ──────────────────────────────────────────────────────────────────

@celery.task
def enqueue_pdf_pages(
    job_id        : str,
    pdf_path      : str,
    mode          : str,
    ekspedisi_mode: str = None,
):
    if not os.path.exists(pdf_path):
        raise Exception(f"File sudah tidak ada: {pdf_path}")

    try:
        print(f"[Job {job_id}] Converting PDF: {pdf_path} | "
              f"ekspedisi={ekspedisi_mode or 'auto'}")
        images = convert_from_path(pdf_path, dpi=150)
        total  = len(images)
        print(f"[Job {job_id}] Total halaman: {total}")

        _init_job(job_id, total, mode)

        tmp_dir = tempfile.mkdtemp(prefix="ocr_pages_")

        for page_num, img_pil in enumerate(images, start=1):
            img_path = os.path.join(tmp_dir, f"page_{page_num}.png")
            img_pil.save(img_path, format="PNG")

            process_page_task.apply_async(
                args  = [job_id, page_num, img_path, mode],
                kwargs= {
                    "pdf_path"      : pdf_path,
                    "ekspedisi_mode": ekspedisi_mode,
                },
                queue = "ocr",
            )
            print(f"[Job {job_id}] Queued page {page_num}/{total} | "
                  f"ekspedisi={ekspedisi_mode or 'auto'}")

        r.hset(_job_key(job_id), "pdf_path", pdf_path)

    except Exception as e:
        print(f"[Job {job_id}] Fatal error saat enqueue: {e}")
        traceback.print_exc()
        r.hset(_job_key(job_id), mapping={"status": "error", "error": str(e)})
        _safe_remove(pdf_path)


# ──────────────────────────────────────────────────────────────────
# Task: cleanup PDF
# ──────────────────────────────────────────────────────────────────

@celery.task
def cleanup_pdf_task(job_id: str):
    meta = r.hgetall(_job_key(job_id))
    pdf_path = meta.get("pdf_path")
    if pdf_path:
        try:
            if os.path.exists(pdf_path):
                os.remove(pdf_path)
                print(f"[Cleanup] PDF dihapus: {pdf_path}")
        except Exception as e:
            print(f"[Cleanup] Gagal hapus PDF: {e}")