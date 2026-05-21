"""
ocr_pdf.py — patched v3
=======================
Fix utama:
  - extract_resi_from_barcode_cv: sebelumnya hanya terima barcode
    yang mengandung 'SPX' → resi SiCepat (pure digits) dibuang.

  Solusi: gunakan expedition_registry.validate_barcode_as_resi()
  sehingga semua ekspedisi yang terdaftar otomatis didukung tanpa
  perlu hardcode filter per ekspedisi di sini.

  Alur baru:
    1. Decode barcode dengan QR detector dan cv2.barcode
    2. Validasi hasil decode via registry → jika match → return resi
    3. Jika tidak ada yang match → return None

  Backward compatible: return value tetap str | None.
"""

import pytesseract
from pdf2image import convert_from_path
import cv2
import numpy as np
import re

# ── Import registry ekspedisi ──────────────────────────────────────────────
from expedition_registry import (
    validate_barcode_as_resi,
    get_barcode_zones,
    EXPEDITION_REGISTRY,
)


# ──────────────────────────────────────────────────────────────────────────────
# Barcode decoder helpers
# ──────────────────────────────────────────────────────────────────────────────

def _try_qr_detector(img_cv: np.ndarray) -> list[str]:
    """Coba decode QR code dengan cv2.QRCodeDetector."""
    results = []
    try:
        qr_detector = cv2.QRCodeDetector()
        data, _, _ = qr_detector.detectAndDecode(img_cv)
        if data:
            results.append(data.strip())
    except Exception as e:
        print(f"[QR] error: {e}")
    return results


def _try_barcode_detector(img_cv: np.ndarray) -> list[str]:
    """Coba decode barcode Code128/EAN/dll dengan cv2.barcode.BarcodeDetector."""
    results = []
    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, decoded_info, decoded_type, _ = bd.detectAndDecodeWithType(img_cv)
        if ok:
            for info in decoded_info:
                if info:
                    results.append(info.strip())
    except AttributeError:
        pass  # cv2.barcode tidak tersedia di build ini
    except Exception as e:
        print(f"[Barcode] error: {e}")
    return results


def _decode_all_barcodes(img_cv: np.ndarray) -> list[str]:
    """
    Coba semua decoder yang tersedia pada sebuah image.
    Return: list teks hasil decode (deduplicated).
    """
    seen = set()
    results = []

    for text in _try_qr_detector(img_cv) + _try_barcode_detector(img_cv):
        if text and text not in seen:
            seen.add(text)
            results.append(text)

    return results


# ──────────────────────────────────────────────────────────────────────────────
# Fungsi utama: extract resi dari barcode
# ──────────────────────────────────────────────────────────────────────────────

def extract_resi_from_barcode_cv(img_cv: np.ndarray,
                                  expedition_key: str = None) -> str | None:
    """
    Extract nomor resi dari barcode/QR yang ada di gambar.

    Mendukung semua ekspedisi yang terdaftar di expedition_registry.py.
    Sebelumnya fungsi ini hanya terima barcode 'SPX...' — sekarang
    validasi dilakukan via registry sehingga SiCepat, JNE, dll otomatis
    didukung tanpa perlu ubah file ini.

    Args:
        img_cv         : numpy array BGR dari cv2.imread / cv2.cvtColor
        expedition_key : Hint ekspedisi (opsional). Jika diketahui dari
                         teks PDF layer, pass ke sini untuk scan lebih
                         akurat. Jika None → coba semua ekspedisi.

    Return: nomor resi (sudah dinormalisasi), atau None.
    """
    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    # Ambil zona crop dari registry
    zones = get_barcode_zones(expedition_key)

    # Preprocessing variants yang dicoba per zona
    def _get_variants(crop_gray, crop_bgr):
        variants = [("orig", crop_bgr)]
        # Scale 3x + OTSU untuk barcode kecil/blur
        scaled = cv2.resize(crop_gray, None, fx=3, fy=3,
                            interpolation=cv2.INTER_CUBIC)
        _, bw = cv2.threshold(scaled, 0, 255,
                              cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        variants.append(("3x_otsu", cv2.cvtColor(bw, cv2.COLOR_GRAY2BGR)))
        # Scale 2x
        scaled2 = cv2.resize(crop_gray, None, fx=2, fy=2,
                             interpolation=cv2.INTER_CUBIC)
        variants.append(("2x", cv2.cvtColor(scaled2, cv2.COLOR_GRAY2BGR)))
        return variants

    for zone_idx, (y0p, y1p, x0p, x1p) in enumerate(zones):
        y0 = int(h * y0p); y1 = int(h * y1p)
        x0 = int(w * x0p); x1 = int(w * x1p)

        if y1 <= y0 or x1 <= x0:
            continue

        crop_gray = gray[y0:y1, x0:x1]
        crop_bgr  = img_cv[y0:y1, x0:x1]

        print(f"[Barcode] Zone {zone_idx} y={y0}-{y1} x={x0}-{x1}")

        for variant_name, var_bgr in _get_variants(crop_gray, crop_bgr):
            decoded_texts = _decode_all_barcodes(var_bgr)

            for text in decoded_texts:
                resi, detected_exp = validate_barcode_as_resi(text, expedition_key)
                if resi:
                    print(f"[Barcode] ✅ Resi={resi!r} exp={detected_exp} "
                          f"zone={zone_idx} variant={variant_name}")
                    return resi
                else:
                    print(f"[Barcode] Bukan resi: {text!r}")

    print(f"[Barcode] Tidak ada resi ditemukan (expedition_key={expedition_key!r})")
    return None


# ──────────────────────────────────────────────────────────────────────────────
# Item parsing dari image (tidak berubah dari versi sebelumnya)
# ──────────────────────────────────────────────────────────────────────────────

def extract_items_from_img_cv(img_cv):
    """
    Parse tabel produk dari numpy array BGR.
    Support nama produk multi-baris (wrap).
    """
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    height, width = gray.shape

    # --- Cari area tabel ---
    table_gray = None
    for start_pct in [0.55, 0.60, 0.65, 0.70]:
        y_start = int(height * start_pct)
        crop = gray[y_start:height, 0:width]
        _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        text = pytesseract.image_to_string(crop_bin, config="--psm 6")
        if re.search(r'nama\s*produk', text, re.IGNORECASE):
            table_gray = crop_bin
            break

    if table_gray is None:
        print("[WARN PDF] Tabel tidak ditemukan")
        return []

    data = pytesseract.image_to_data(
        table_gray,
        output_type=pytesseract.Output.DICT,
        config="--psm 6"
    )

    # --- Cluster words by Y position ---
    words_list = []
    for i, word in enumerate(data['text']):
        if not word.strip() or data['conf'][i] < 0:
            continue
        words_list.append({
            'text': word,
            'x'   : data['left'][i],
            'y'   : data['top'][i],
            'w'   : data['width'][i],
            'h'   : data['height'][i],
        })

    if not words_list:
        return []

    words_list.sort(key=lambda w: w['y'])
    rows = []
    current_row = [words_list[0]]

    for w in words_list[1:]:
        avg_y = sum(r['y'] for r in current_row) / len(current_row)
        if abs(w['y'] - avg_y) <= 20:
            current_row.append(w)
        else:
            current_row.sort(key=lambda r: r['x'])
            rows.append(current_row)
            current_row = [w]
    if current_row:
        current_row.sort(key=lambda r: r['x'])
        rows.append(current_row)

    # --- Cari header row ---
    header_row = None
    for row in rows:
        line_text = " ".join(w['text'] for w in row)
        if re.search(r'nama.{0,5}produk', line_text, re.IGNORECASE):
            header_row = row
            break

    if not header_row:
        print("[WARN PDF] Header tabel tidak ditemukan")
        return []

    # --- Posisi kolom dari header ---
    col_x = {}
    for w in header_row:
        t = w['text'].lower().strip('#').strip()
        if t == 'sku':
            col_x['sku'] = w['x']
        elif t in ('variasi', 'varian'):
            col_x['variasi'] = w['x']
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x']

    if not col_x.get('sku'):
        print("[WARN PDF] Kolom SKU tidak ditemukan")
        return []

    sku_x     = col_x['sku']
    variasi_x = col_x.get('variasi', width * 0.65)
    qty_x     = col_x.get('qty',     width * 0.85)
    header_y  = header_row[0]['y']

    item_groups: list[list] = []

    for row in rows:
        if row[0]['y'] <= header_y:
            continue

        line_text = " ".join(w['text'] for w in row)

        if re.match(r'Pesan\s*:', line_text, re.IGNORECASE):
            break

        leftmost = min(row, key=lambda w: w['x'])
        line_starts_with_num = bool(re.match(r'^\d{1,2}[\s\.]', line_text))
        leftmost_in_nama_zone = leftmost['x'] < sku_x

        if not line_starts_with_num:
            first_word = row[0]['text'] if row else ''
            line_starts_with_num = bool(re.match(r'^\d{1,2}$', first_word))

        is_new_item = line_starts_with_num and leftmost_in_nama_zone

        print(f"[ROW] y={row[0]['y']} is_new={is_new_item} text={repr(line_text[:60])}")

        if is_new_item:
            item_groups.append([row])
        elif item_groups:
            item_groups[-1].append(row)

    items = []

    for group in item_groups:
        nama_parts  = []
        sku_parts   = []
        var_parts   = []
        qty_parts   = []

        for row in group:
            for w in row:
                x = w['x']
                t = w['text']
                if x < sku_x - 10:
                    nama_parts.append(t)
                elif x < variasi_x - 10:
                    sku_parts.append(t)
                elif x < qty_x - 10:
                    var_parts.append(t)
                else:
                    qty_parts.append(t)

        nama    = re.sub(r'^\d+\s*', '', " ".join(nama_parts)).strip()
        sku     = " ".join(sku_parts).strip()
        variasi = " ".join(var_parts).strip()
        qty_str = " ".join(qty_parts).strip()

        if not sku:
            print(f"[SKIP grup, no SKU] nama={repr(nama)}")
            continue

        try:
            qty = int(re.sub(r'\D', '', qty_str)) if qty_str else 1
        except ValueError:
            qty = 1

        print(f"[PDF ITEM] nama={repr(nama)} sku={repr(sku)} variasi={repr(variasi)} qty={qty}")
        items.append({
            "nama"   : nama,
            "sku"    : sku,
            "variasi": variasi,
            "qty"    : qty,
        })

    return items


def extract_text_pdf(pdf_path):
    """Return (text, items)"""
    images = convert_from_path(pdf_path, dpi=300)
    full_text = ""
    all_items = []

    for img_pil in images:
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)

        resi = extract_resi_from_barcode_cv(img_cv)

        gray   = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
        ocr_text = pytesseract.image_to_string(thresh)

        if resi:
            full_text += f"No.Resi: {resi}\n" + ocr_text + "\n"
        else:
            full_text += ocr_text + "\n"

        page_items = extract_items_from_img_cv(img_cv)
        all_items.extend(page_items)

    return full_text, all_items