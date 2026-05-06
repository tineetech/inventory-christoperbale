"""
tasks.py — patched v2
Fix utama:
  1. process_page_task sekarang menerima pdf_path opsional
     → diteruskan ke _process_single_page_cv supaya bisa extract
     teks dari PDF text layer (jauh lebih cepat dari OCR).

  2. enqueue_pdf_pages: simpan setiap halaman sebagai PNG,
     tapi JUGA pass pdf_path ke task supaya text layer bisa dibaca.
     
  3. Catatan arsitektur:
     - PDF path dikirim via args ke task (bukan Redis) karena
       file harus tetap ada saat task jalan di thread pool.
     - PDF asli TIDAK dihapus di enqueue_pdf_pages, melainkan
       dihapus setelah SEMUA halaman selesai (atau TTL expired).
     - Untuk single-image task, pdf_path = None (tidak berubah).
"""
from dotenv import load_dotenv
import uuid
import json
import traceback
import tempfile
import os

import cv2
import numpy as np
import redis

from celery_app import celery

from pdf2image import convert_from_path
from ocr_shopee_multiple import _process_single_page_cv, _cv_to_base64_jpeg
from ocr_tiktok_multiple import _process_single_page as _process_tiktok_page

load_dotenv()
# ──────────────────────────────────────────────────────────────────
# Redis langsung (untuk update progress parsial per halaman)
# ──────────────────────────────────────────────────────────────────
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")
r = redis.from_url(REDIS_URL, decode_responses=True)

JOB_TTL = 3600  # 1 jam


# ──────────────────────────────────────────────────────────────────
# Helper: simpan progress job ke Redis
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


# ──────────────────────────────────────────────────────────────────
# Task: proses 1 halaman
# BARU: terima pdf_path opsional untuk text layer extraction
# ──────────────────────────────────────────────────────────────────

@celery.task(bind=True, max_retries=2, default_retry_delay=5)
def process_page_task(self, job_id: str, page_num: int, img_path: str, mode: str,
                      pdf_path: str = None):
    """
    Proses satu halaman resi dari file gambar sementara.
    img_path : path PNG tempfile halaman yang sudah di-render.
    pdf_path : path PDF asli (opsional) — untuk extract text layer langsung.
               Lebih cepat dan akurat daripada OCR Tesseract.
    """

    if not os.path.exists(img_path):
        raise Exception(f"File sudah tidak ada: {img_path}")

    try:
        img_cv = cv2.imread(img_path)
        if img_cv is None:
            raise ValueError(f"Gagal baca gambar: {img_path}")

        if mode == "shopee":
            # ── FIX: pass pdf_path supaya text layer extraction bisa jalan ──
            result = _process_single_page_cv(img_cv, page_num, pdf_path=pdf_path)
        elif mode == "tiktok":
            # TikTok resi tidak punya text layer yang berguna, tetap OCR
            result = _process_tiktok_page(img_cv, page_num, pdf_path=pdf_path)
        else:
            raise ValueError(f"mode tidak dikenal: {mode}")

        _save_page_result(job_id, page_num, result)

    except Exception as exc:
        print(f"[Task] Page {page_num} error: {exc}")
        traceback.print_exc()
        try:
            raise self.retry(exc=exc, max_retries=3, countdown=5)
        except self.MaxRetriesExceededError:
            _mark_page_failed(job_id, page_num, str(exc))
    finally:
        # Hapus PNG temporer setelah diproses
        try:
            if os.path.exists(img_path):
                os.remove(img_path)
        except Exception:
            pass
        # JANGAN hapus pdf_path di sini — mungkin masih dipakai halaman lain


# ──────────────────────────────────────────────────────────────────
# Task: entry point — terima PDF, split, queue tiap halaman
# BARU: pass pdf_path ke tiap process_page_task
# ──────────────────────────────────────────────────────────────────

@celery.task
def enqueue_pdf_pages(job_id: str, pdf_path: str, mode: str):
    """
    Convert PDF → halaman PNG → queue process_page_task per halaman.
    
    FIX v2:
    - pdf_path TIDAK dihapus di sini, melainkan diteruskan ke tiap task
      supaya text layer bisa dibaca.
    - PDF dihapus via cleanup task atau TTL Redis (1 jam).
    - Untuk safety, track jumlah task yang sudah queue dan hapus PDF
      hanya setelah semua task selesai (via Redis counter).
    """

    if not os.path.exists(pdf_path):
        raise Exception(f"File sudah tidak ada: {pdf_path}")

    try:
        print(f"[Job {job_id}] Converting PDF: {pdf_path}")
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
                kwargs= {"pdf_path": pdf_path},    # ← PASS pdf_path
                queue = "ocr",
            )
            print(f"[Job {job_id}] Queued page {page_num}/{total} "
                  f"(pdf_path={pdf_path})")

        # Simpan pdf_path di Redis supaya bisa di-cleanup setelah semua selesai
        r.hset(_job_key(job_id), "pdf_path", pdf_path)

    except Exception as e:
        print(f"[Job {job_id}] Fatal error saat enqueue: {e}")
        traceback.print_exc()
        r.hset(_job_key(job_id), mapping={
            "status": "error",
            "error" : str(e),
        })
        # Hapus PDF jika gagal total
        try:
            if os.path.exists(pdf_path):
                os.remove(pdf_path)
        except Exception:
            pass


# ──────────────────────────────────────────────────────────────────
# Task: cleanup PDF setelah job done
# Dipanggil dari job_status endpoint ketika status=done
# ──────────────────────────────────────────────────────────────────

@celery.task
def cleanup_pdf_task(job_id: str):
    """Hapus PDF asli setelah semua halaman selesai diproses."""
    meta = r.hgetall(_job_key(job_id))
    pdf_path = meta.get("pdf_path")
    if pdf_path:
        try:
            if os.path.exists(pdf_path):
                os.remove(pdf_path)
                print(f"[Cleanup] PDF dihapus: {pdf_path}")
        except Exception as e:
            print(f"[Cleanup] Gagal hapus PDF: {e}")