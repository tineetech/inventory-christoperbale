"""
PATCH untuk ocr_shopee_multiple.py
Masalah: image_base64 kosong karena kemungkinan:
  1. Memory error saat encode gambar besar (DPI 200 + 6 halaman)
  2. Exception silent di _cv_to_base64_jpeg

Fix:
  - Resize gambar ke max 900px width sebelum encode
  - Quality turun ke 70
  - Try/except eksplisit dengan print error
  - Log panjang base64 tiap halaman
"""

import re
import cv2
import numpy as np
import pytesseract
import base64
import traceback
from pdf2image import convert_from_path

from ocr_pdf import extract_resi_from_barcode_cv, extract_items_from_img_cv
from parser.parser_shopee import parse_shopee
from ocr_pdf_patch import extract_order_id_from_barcode


def _cv_to_base64_jpeg(img_cv: np.ndarray, quality: int = 70, max_width: int = 900) -> str:
    """
    Konversi numpy BGR array ke base64 JPEG string.
    Resize otomatis jika lebar > max_width supaya payload tidak terlalu besar.
    """
    try:
        h, w = img_cv.shape[:2]

        # Resize kalau terlalu lebar
        if w > max_width:
            scale   = max_width / w
            new_w   = max_width
            new_h   = int(h * scale)
            img_cv  = cv2.resize(img_cv, (new_w, new_h), interpolation=cv2.INTER_AREA)
            print(f"[base64] Resize {w}x{h} → {new_w}x{new_h}")

        encode_params = [cv2.IMWRITE_JPEG_QUALITY, quality]
        ok, buf = cv2.imencode('.jpg', img_cv, encode_params)

        if not ok:
            print("[base64] ❌ cv2.imencode gagal")
            return ''

        result = base64.b64encode(buf.tobytes()).decode('utf-8')
        print(f"[base64] ✅ encoded len={len(result)}")
        return result

    except Exception as e:
        print(f"[base64] ❌ Exception: {e}")
        traceback.print_exc()
        return ''


def _process_single_page_cv(img_cv: np.ndarray, page_num: int) -> dict:
    """
    Proses 1 cv2 BGR image → hasil parse 1 resi.
    Menyertakan image_base64 dari halaman aslinya.
    """
    print(f"\n[Page {page_num}] Mulai proses...")

    # Encode gambar DULU sebelum proses OCR (supaya img_cv belum diubah)
    print(f"[Page {page_num}] Encoding image to base64...")
    image_b64 = _cv_to_base64_jpeg(img_cv)
    print(f"[Page {page_num}] image_base64 length = {len(image_b64)}")

    # Barcode / QR
    resi_from_barcode = extract_resi_from_barcode_cv(img_cv)

    # OCR text
    gray     = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    thresh   = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
    ocr_text = pytesseract.image_to_string(thresh)

    if resi_from_barcode:
        full_text = f"No.Resi: {resi_from_barcode}\n" + ocr_text
    else:
        full_text = ocr_text

    # Item parsing
    items = extract_items_from_img_cv(img_cv)

    # Parse shopee
    result = parse_shopee(full_text, items)
    order_id_from_barcode = extract_order_id_from_barcode(img_cv)
    if order_id_from_barcode:
        result["order_id"] = order_id_from_barcode
    result["page"]         = page_num
    result["image_base64"] = image_b64   # ← SELALU ada, minimal string kosong

    print(f"[Page {page_num}] resi={result.get('resi')} order_id={result.get('order_id')} "
          f"items={len(items)} image_b64_len={len(image_b64)}")

    return result


def extract_multiple_resi_from_pdf(pdf_path: str) -> list[dict]:
    # DPI 150 cukup untuk OCR, lebih hemat memory dari 200
    print(f"[PDF Multiple] Converting PDF: {pdf_path}")
    images  = convert_from_path(pdf_path, dpi=150)
    results = []

    print(f"[PDF Multiple] Total {len(images)} halaman")

    for page_num, img_pil in enumerate(images, start=1):
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
        try:
            result = _process_single_page_cv(img_cv, page_num)
        except Exception as e:
            print(f"[PDF Multiple] ❌ Page {page_num} error: {e}")
            traceback.print_exc()
            result = {
                "page"        : page_num,
                "resi"        : None,
                "order_id"    : None,
                "items"       : [],
                "skus"        : [],
                "image_base64": _cv_to_base64_jpeg(img_cv),  # tetap kirim gambar meski parse gagal
            }
        results.append(result)

    print(f"[PDF Multiple] Selesai. {len(results)} halaman diproses.")
    return results


def extract_multiple_resi_from_image(image_path: str) -> list[dict]:
    img_cv  = cv2.imread(image_path)
    h, w    = img_cv.shape[:2]
    results = []
    aspect  = w / h

    if aspect > 1.6:
        print(f"[Image Multiple] Layout 2-kolom (aspect={aspect:.2f})")
        mid_x  = w // 2
        slices = [img_cv[:, 0:mid_x], img_cv[:, mid_x:w]]
        for idx, slice_cv in enumerate(slices, start=1):
            results.append(_process_single_page_cv(slice_cv, idx))

    elif aspect < 0.5:
        estimated_unit_h = 600
        n_units = max(1, round(h / estimated_unit_h))
        print(f"[Image Multiple] {n_units} resi vertikal (aspect={aspect:.2f})")
        unit_h = h // n_units
        for idx in range(n_units):
            y1 = idx * unit_h
            y2 = (idx + 1) * unit_h if idx < n_units - 1 else h
            results.append(_process_single_page_cv(img_cv[y1:y2, 0:w], idx + 1))

    else:
        print(f"[Image Multiple] Single resi (aspect={aspect:.2f})")
        results.append(_process_single_page_cv(img_cv, 1))

    print(f"[Image Multiple] Total {len(results)} resi dari 1 file gambar")
    return results