"""
ocr_shopee_multiple.py — patched v4
====================================
Fix v4 vs v3:
  - _process_single_page_cv sekarang menerima ekspedisi_mode (opsional).
    Jika diisi → skip auto-detect, langsung pakai registry ekspedisi tsb
    via process_label(). Eliminasi false-positive antar ekspedisi.
  - Jika ekspedisi_mode=None → perilaku auto-detect v3 tetap jalan.
  - Tidak ada perubahan lain.
"""

import re
import cv2
import numpy as np
import pytesseract
import base64
import traceback
import threading
import os
from concurrent.futures import ThreadPoolExecutor, TimeoutError as FuturesTimeoutError
from pdf2image import convert_from_path

from ocr_pdf import extract_resi_from_barcode_cv, extract_items_from_img_cv
from parser.parser_shopee import parse_shopee
from ocr_pdf_patch import extract_order_id_from_barcode

# ── Import registry ekspedisi ──────────────────────────────────────────────
from expedition_registry import (
    process_label,               # ← v4: entry point utama
    detect_expedition_from_text,
    extract_resi_from_text,
    validate_barcode_as_resi,
    EXPEDITION_REGISTRY,
)

import threading
_pdfplumber_lock = threading.Lock()


# ──────────────────────────────────────────────────────────────────────────────
# Timeout wrapper (tidak berubah dari v3)
# ──────────────────────────────────────────────────────────────────────────────

class TesseractTimeout(Exception):
    pass


_tess_executor = ThreadPoolExecutor(max_workers=4, thread_name_prefix="tess_")


def run_tesseract_safe(img, config="", timeout_sec=30):
    future = _tess_executor.submit(
        pytesseract.image_to_string, img, **{"config": config}
    )
    try:
        return future.result(timeout=timeout_sec) or ""
    except FuturesTimeoutError:
        future.cancel()
        raise TesseractTimeout(f"pytesseract timeout setelah {timeout_sec}s")


def run_tesseract_data_safe(img, output_type, config="", timeout_sec=30):
    future = _tess_executor.submit(
        pytesseract.image_to_data, img,
        **{"output_type": output_type, "config": config}
    )
    try:
        return future.result(timeout=timeout_sec)
    except FuturesTimeoutError:
        future.cancel()
        raise TesseractTimeout(f"pytesseract image_to_data timeout setelah {timeout_sec}s")


# ──────────────────────────────────────────────────────────────────────────────
# PDF text layer extraction (tidak berubah dari v3)
# ──────────────────────────────────────────────────────────────────────────────

def _extract_text_and_items_from_pdf_layer(pdf_path: str, page_num: int) -> tuple[str, list]:
    try:
        import pdfplumber
    except ImportError:
        return "", []

    try:
        with _pdfplumber_lock:
            with pdfplumber.open(pdf_path) as pdf:
                if page_num < 1 or page_num > len(pdf.pages):
                    return "", []
                page = pdf.pages[page_num - 1]
                full_text = page.extract_text() or ""
                if not full_text.strip():
                    return "", []
                print(f"[PDF Layer] Page {page_num}: {len(full_text)} chars OK")
                items = _parse_items_by_word_position(page)
                return full_text, items
    except Exception as e:
        print(f"[PDF Layer] Error: {e}")
        return "", []


def _parse_items_by_word_position(page) -> list:
    try:
        words = page.extract_words(
            x_tolerance=3, y_tolerance=3,
            keep_blank_chars=False, use_text_flow=False,
        )
    except Exception as e:
        print(f"[PDF Layer Words] extract_words error: {e}")
        return []

    if not words:
        return []

    words.sort(key=lambda w: (w['top'], w['x0']))
    rows = []
    cur_row = [words[0]]
    for w in words[1:]:
        avg_top = sum(r['top'] for r in cur_row) / len(cur_row)
        if abs(w['top'] - avg_top) <= 5:
            cur_row.append(w)
        else:
            cur_row.sort(key=lambda r: r['x0'])
            rows.append(cur_row)
            cur_row = [w]
    if cur_row:
        cur_row.sort(key=lambda r: r['x0'])
        rows.append(cur_row)

    header_row = None
    header_row_idx = None
    for i, row in enumerate(rows):
        line = " ".join(w['text'] for w in row)
        if re.search(r'#\s*Nama\s*Produk', line, re.IGNORECASE):
            header_row = row
            header_row_idx = i
            break

    if header_row is None:
        return _parse_items_from_pdf_text_fallback(
            "\n".join(" ".join(w['text'] for w in r) for r in rows)
        )

    page_width = page.width
    col_x = {}
    for w in header_row:
        t = w['text'].lower().strip('#').strip()
        if t == 'sku':
            col_x['sku'] = w['x0']
        elif t in ('variasi', 'varian'):
            col_x['variasi'] = w['x0']
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x0']

    x_sku     = col_x.get('sku',     page_width * 0.45)
    x_variasi = col_x.get('variasi', page_width * 0.62)
    x_qty     = col_x.get('qty',     page_width * 0.85)

    items = []
    current_item = None

    for row in rows[header_row_idx + 1:]:
        line = " ".join(w['text'] for w in row)

        if re.match(r'Pesan\s*:', line, re.IGNORECASE):
            break

        nama_words    = []
        sku_words     = []
        variasi_words = []
        qty_words     = []

        for w in row:
            x = w['x0']
            if x < x_sku:
                nama_words.append(w['text'])
            elif x < x_variasi:
                sku_words.append(w['text'])
            elif x < x_qty:
                variasi_words.append(w['text'])
            else:
                qty_words.append(w['text'])

        nama_line    = " ".join(nama_words).strip()
        sku_line     = " ".join(sku_words).strip()
        variasi_line = " ".join(variasi_words).strip()
        qty_line     = " ".join(qty_words).strip()

        is_new_item = bool(re.match(r'^\d+\s', nama_line))

        if is_new_item:
            if current_item:
                items.append(current_item)
            nama_clean = re.sub(r'^\d+\s+', '', nama_line).strip()
            qty = 1
            if qty_line:
                qty_digits = re.sub(r'\D', '', qty_line)
                try:
                    qty = int(qty_digits) if qty_digits else 1
                except ValueError:
                    qty = 1
            current_item = {
                "nama": nama_clean, "sku": sku_line,
                "variasi": variasi_line, "qty": qty,
                "_qty_set": qty_line != "",
            }
        elif current_item:
            if nama_line:
                current_item["nama"] = (current_item["nama"] + " " + nama_line).strip()
            if sku_line and not current_item["sku"]:
                current_item["sku"] = sku_line
            if variasi_line and not current_item["variasi"]:
                current_item["variasi"] = variasi_line
            if qty_line and not current_item.get("_qty_set"):
                qty_digits = re.sub(r'\D', '', qty_line)
                try:
                    current_item["qty"] = int(qty_digits) if qty_digits else 1
                    current_item["_qty_set"] = True
                except ValueError:
                    pass

    if current_item:
        items.append(current_item)

    for item in items:
        item.pop("_qty_set", None)

    return items


def _parse_items_from_pdf_text_fallback(text: str) -> list:
    items = []
    lines = text.split('\n')
    SKU_RE = re.compile(r'\b([a-z][a-z0-9\-_]{3,19})\b')
    VARIASI_QTY_RE = re.compile(
        r'([A-Za-z][A-Za-z\s]*,\s*\d{2,3})\s+(\d+)\s*$'
    )
    for line in lines:
        line = line.strip()
        if not line:
            continue
        m_tail = VARIASI_QTY_RE.search(line)
        if not m_tail:
            continue
        variasi = m_tail.group(1).strip()
        qty     = int(m_tail.group(2))
        before  = line[:m_tail.start()].strip()
        before  = re.sub(r'^\d+\s+', '', before).strip()
        tokens  = before.split()
        sku     = ""
        nama_tokens = []
        for token in tokens:
            if SKU_RE.fullmatch(token) and token.islower():
                sku = token
            else:
                nama_tokens.append(token)
        if sku:
            nama = " ".join(t for t in nama_tokens if t != sku).strip()
        else:
            nama = before
        if not nama:
            continue
        items.append({"nama": nama, "sku": sku, "variasi": variasi, "qty": qty})
    return items


def _parse_items_from_pdf_text(text: str) -> list:
    return _parse_items_from_pdf_text_fallback(text)


# ──────────────────────────────────────────────────────────────────────────────
# Image helpers (tidak berubah dari v3)
# ──────────────────────────────────────────────────────────────────────────────

def _cv_to_base64_jpeg(img_cv: np.ndarray, quality: int = 70, max_width: int = 900) -> str:
    try:
        h, w = img_cv.shape[:2]
        if w > max_width:
            scale  = max_width / w
            img_cv = cv2.resize(img_cv, (max_width, int(h * scale)),
                                interpolation=cv2.INTER_AREA)
        ok, buf = cv2.imencode('.jpg', img_cv, [cv2.IMWRITE_JPEG_QUALITY, quality])
        if not ok:
            return ''
        return base64.b64encode(buf.tobytes()).decode('utf-8')
    except Exception as e:
        print(f"[base64] Exception: {e}")
        return ''


def _extract_items_safe(img_cv: np.ndarray, timeout_sec: int = 45) -> list:
    future = _tess_executor.submit(extract_items_from_img_cv, img_cv)
    try:
        return future.result(timeout=timeout_sec) or []
    except FuturesTimeoutError:
        future.cancel()
        raise TesseractTimeout(f"extract_items timeout setelah {timeout_sec}s")


# ──────────────────────────────────────────────────────────────────────────────
# Core processor — v4: ekspedisi_mode aware
# ──────────────────────────────────────────────────────────────────────────────

def _process_single_page_cv(
    img_cv        : np.ndarray,
    page_num      : int,
    pdf_path      : str = None,
    ekspedisi_mode: str = None,   # ← TAMBAHAN v4
) -> dict:
    """
    Proses 1 cv2 BGR image → hasil parse 1 resi.

    ekspedisi_mode : kunci ekspedisi yang sudah diketahui ("jne", "sicepat", dst).
                     Jika diisi → skip auto-detect Layer 1/2/3, langsung pakai
                     registry ekspedisi tsb. Eliminasi false-positive.
                     Jika None → auto-detect seperti v3.
    """
    print(f"\n[Page {page_num}] Mulai proses... ekspedisi_mode={ekspedisi_mode or 'auto'}")

    image_b64 = _cv_to_base64_jpeg(img_cv)

    # ── Step 1: Extract teks (PDF layer atau OCR) ──────────────────────────
    pdf_text  = ""
    pdf_items = []
    if pdf_path:
        pdf_text, pdf_items = _extract_text_and_items_from_pdf_layer(pdf_path, page_num)

    if pdf_text.strip():
        ocr_text = pdf_text
        items    = pdf_items
        print(f"[Page {page_num}] ✅ Pakai PDF text layer, skip OCR")
    else:
        print(f"[Page {page_num}] ⚠ Fallback ke OCR Tesseract...")
        gray   = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
        try:
            ocr_text = run_tesseract_safe(thresh, timeout_sec=30)
        except TesseractTimeout:
            ocr_text = ""
        except Exception as e:
            print(f"[Page {page_num}] OCR error: {e}")
            ocr_text = ""

        try:
            items = _extract_items_safe(img_cv, timeout_sec=45)
        except (TesseractTimeout, Exception) as e:
            print(f"[Page {page_num}] Item parsing error: {e}")
            items = []

    # ── Step 2: Deteksi ekspedisi + extract resi dari teks ────────────────
    # v4: gunakan process_label() — kalau ekspedisi_mode diisi, skip auto-detect
    expedition_key, resi_from_text = process_label(ocr_text, ekspedisi_mode=ekspedisi_mode)
    print(f"[Page {page_num}] Ekspedisi terdeteksi: {expedition_key!r}")

    # ── Step 3: Scan barcode dengan hint ekspedisi ─────────────────────────
    print(f"[Page {page_num}] Scanning barcode (expedition={expedition_key!r})...")
    resi_from_barcode = extract_resi_from_barcode_cv(img_cv, expedition_key)

    # Barcode lebih dipercaya daripada teks — pakai barcode kalau ada
    resi = resi_from_barcode or resi_from_text
    if resi_from_barcode:
        print(f"[Page {page_num}] ✅ Resi dari barcode: {resi_from_barcode!r}")
    elif resi_from_text:
        print(f"[Page {page_num}] ✅ Resi dari teks: {resi_from_text!r}")

    # ── Step 4: Gabungkan ke full_text ────────────────────────────────────
    if resi:
        full_text = f"No.Resi: {resi}\n" + ocr_text
    else:
        full_text = ocr_text

    # ── Step 5: Parse Shopee (order_id, items, skus) ──────────────────────
    result = parse_shopee(full_text, items)

    if resi:
        result["resi"] = resi

    # ── Step 6: Order ID dari barcode ─────────────────────────────────────
    order_id_from_barcode = None
    try:
        order_id_from_barcode = extract_order_id_from_barcode(img_cv)
        print(f"[Page {page_num}] order_id_from_barcode={order_id_from_barcode!r}")
    except Exception as e:
        print(f"[Page {page_num}] Order ID barcode error: {e}")

    if order_id_from_barcode:
        result["order_id"] = order_id_from_barcode
    elif not result.get("order_id"):
        m = re.search(r'No\.?\s*Pesanan[:\s]+([\w\-]+)', full_text, re.IGNORECASE)
        if m:
            result["order_id"] = m.group(1).strip()

    result["page"]         = page_num
    result["image_base64"] = image_b64
    result["expedition"]   = expedition_key

    print(f"[Page {page_num}] ✅ SELESAI — resi={result.get('resi')} "
          f"order_id={result.get('order_id')} exp={expedition_key} "
          f"items={len(result.get('items', []))}")

    return result


# ──────────────────────────────────────────────────────────────────────────────
# Public API (tidak berubah signature-nya)
# ──────────────────────────────────────────────────────────────────────────────

def extract_multiple_resi_from_pdf(pdf_path: str) -> list[dict]:
    print(f"[PDF Multiple] Converting PDF: {pdf_path}")
    images  = convert_from_path(pdf_path, dpi=150)
    results = []

    for page_num, img_pil in enumerate(images, start=1):
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
        try:
            result = _process_single_page_cv(img_cv, page_num, pdf_path=pdf_path)
        except Exception as e:
            print(f"[PDF Multiple] ❌ Page {page_num} error: {e}")
            traceback.print_exc()
            result = {
                "page"        : page_num,
                "resi"        : None,
                "order_id"    : None,
                "items"       : [],
                "skus"        : [],
                "expedition"  : None,
                "image_base64": _cv_to_base64_jpeg(img_cv),
            }
        results.append(result)

    return results


def extract_multiple_resi_from_image(image_path: str) -> list[dict]:
    img_cv = cv2.imread(image_path)
    h, w   = img_cv.shape[:2]
    results = []
    aspect  = w / h

    if aspect > 1.6:
        mid_x  = w // 2
        slices = [img_cv[:, 0:mid_x], img_cv[:, mid_x:w]]
        for idx, slice_cv in enumerate(slices, start=1):
            results.append(_process_single_page_cv(slice_cv, idx))
    elif aspect < 0.5:
        n_units = max(1, round(h / 600))
        unit_h  = h // n_units
        for idx in range(n_units):
            y1 = idx * unit_h
            y2 = (idx + 1) * unit_h if idx < n_units - 1 else h
            results.append(_process_single_page_cv(img_cv[y1:y2, 0:w], idx + 1))
    else:
        results.append(_process_single_page_cv(img_cv, 1))

    return results