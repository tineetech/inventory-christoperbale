"""
tasks.py — patched v4
Fix v4 vs v3:
  1. Fix retry bug: PNG tidak boleh dihapus di finally kalau task masih
     akan di-retry. Sebelumnya finally selalu hapus PNG → retry gagal
     karena file sudah tidak ada.
     Solusi: hapus PNG hanya di blok else (sukses) atau MaxRetriesExceededError.

  2. ekspedisi_mode diteruskan ke _process_single_page_cv (dari v3).
"""
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


# ──────────────────────────────────────────────────────────────────
# Helper Redis (tidak berubah dari v3)
# ──────────────────────────────────────────────────────────────────

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


def _save_page_result(job_id: str, page_num: int, result: dict):
    pipe = r.pipeline()
    pipe.rpush(f"ocr_job:{job_id}:pages", json.dumps(result))
    pipe.hincrby(_job_key(job_id), "done_pages", 1)
    pipe.expire(f"ocr_job:{job_id}:pages", JOB_TTL)
    pipe.execute()

    meta  = r.hgetall(_job_key(job_id))
    done  = int(meta.get("done_pages", 0)) + int(meta.get("failed_pages", 0))
    total = int(meta.get("total_pages", 0))
    if done >= total:
        r.hset(_job_key(job_id), "status", "done")


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

    meta  = r.hgetall(_job_key(job_id))
    done  = int(meta.get("done_pages", 0)) + int(meta.get("failed_pages", 0))
    total = int(meta.get("total_pages", 0))
    if done >= total:
        r.hset(_job_key(job_id), "status", "done")


def _safe_remove(path: str):
    """Hapus file tanpa raise exception."""
    try:
        if path and os.path.exists(path):
            os.remove(path)
    except Exception as e:
        print(f"[Cleanup] Gagal hapus {path}: {e}")


# ──────────────────────────────────────────────────────────────────
# Task: proses 1 halaman
# v4: fix retry bug PNG + ekspedisi_mode
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
    """
    Proses satu halaman resi dari file gambar sementara.

    FIX v4: PNG hanya dihapus setelah sukses atau MaxRetriesExceededError.
            Sebelumnya dihapus di finally → retry selalu gagal file not found.
    """
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

        # ✅ Sukses — baru hapus PNG
        _safe_remove(img_path)

    except Exception as exc:
        print(f"[Task] Page {page_num} error: {exc}")
        traceback.print_exc()
        try:
            # Masih bisa retry — JANGAN hapus PNG dulu
            raise self.retry(exc=exc, countdown=5)
        except self.MaxRetriesExceededError:
            # Habis retry — hapus PNG, tandai failed
            _safe_remove(img_path)
            _mark_page_failed(job_id, page_num, str(exc))

    # TIDAK ada finally block — PNG diurus di blok sukses / MaxRetriesExceededError


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
# Task: cleanup PDF setelah job done (tidak berubah)
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