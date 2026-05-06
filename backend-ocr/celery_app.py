"""
celery_app.py — patched
Fix utama: gunakan worker_pool='threads' supaya Tesseract tidak
deadlock di WSL2/Linux fork-worker environment.

Kenapa 'threads' bukan default 'prefork':
  - prefork = fork() proses Python yang sudah ada → shared state
    (mutex Tesseract, file handle) ter-copy → deadlock saat OCR
  - threads = pakai threading dalam 1 proses → tidak fork → aman

Trade-off: GIL Python membatasi true parallelism untuk CPU-bound,
tapi Tesseract melepas GIL saat OCR (C extension) → tetap bisa
jalan paralel secara efektif.
"""

from celery import Celery
from dotenv import load_dotenv
import os

load_dotenv()

REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")

celery = Celery(
    "ocr_tasks",
    broker=REDIS_URL,
    backend=REDIS_URL,
    include=["tasks"],
)

celery.conf.update(
    # Serializer
    task_serializer="json",
    result_serializer="json",
    accept_content=["json"],

    # Result TTL 1 jam
    result_expires=3600,

    # ★ FIX UTAMA: threads pool — tidak fork, aman untuk Tesseract di WSL2
    worker_pool="threads",

    # Jumlah thread paralel (4 cukup untuk OCR berat)
    worker_concurrency=4,

    # Ambil 1 task sekaligus
    worker_prefetch_multiplier=1,

    # Suppress DeprecationWarning di log
    broker_connection_retry_on_startup=True,

    # Timezone
    timezone="Asia/Jakarta",
    enable_utc=True,
)