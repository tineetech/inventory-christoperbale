import pytesseract
from pdf2image import convert_from_path
import cv2
import numpy as np
import re


def extract_resi_from_barcode_cv(img_cv):
    qr_detector = cv2.QRCodeDetector()
    data, _, _ = qr_detector.detectAndDecode(img_cv)
    if data and re.search(r'SPX', data, re.IGNORECASE):
        print(f"[QR] {repr(data)}")
        return data.strip()

    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, decoded_info, decoded_type, _ = bd.detectAndDecodeWithType(img_cv)
        if ok:
            for info in decoded_info:
                if info and re.search(r'SPX', info, re.IGNORECASE):
                    print(f"[Barcode] {repr(info)}")
                    return info.strip()

        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        h, w = gray.shape
        crop = gray[int(h*0.15):int(h*0.40), 0:w]
        crop = cv2.resize(crop, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
        _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        crop_bgr = cv2.cvtColor(crop_bin, cv2.COLOR_GRAY2BGR)
        ok2, decoded_info2, _, _ = bd.detectAndDecodeWithType(crop_bgr)
        if ok2:
            for info in decoded_info2:
                if info and re.search(r'SPX', info, re.IGNORECASE):
                    print(f"[Barcode crop] {repr(info)}")
                    return info.strip()
    except AttributeError:
        print("[WARN] cv2.barcode tidak tersedia")

    return None


def extract_items_from_img_cv(img_cv):
    """Parse tabel produk dari numpy array BGR — logic sama dengan extract_shopee_items di ocr.py"""
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    height, width = gray.shape

    # Cari area tabel
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

    # Cluster words by Y position
    words_list = []
    for i, word in enumerate(data['text']):
        if not word.strip() or data['conf'][i] < 0:
            continue
        words_list.append({
            'text': word,
            'x': data['left'][i],
            'y': data['top'][i],
            'w': data['width'][i],
            'h': data['height'][i],
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

    # Cari header
    header_row = None
    for row in rows:
        line_text = " ".join(w['text'] for w in row)
        if re.search(r'nama.{0,5}produk', line_text, re.IGNORECASE):
            header_row = row
            break

    if not header_row:
        print("[WARN PDF] Header tabel tidak ditemukan")
        return []

    # Posisi kolom
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
    qty_x     = col_x.get('qty', width * 0.85)
    header_y  = header_row[0]['y']

    items = []
    for row in rows:
        if row[0]['y'] <= header_y:
            continue

        line_text = " ".join(w['text'] for w in row)

        if re.match(r'Pesan\s*:', line_text, re.IGNORECASE):
            break
        if not re.match(r'^\d+', line_text):
            continue

        nama_words, sku_words, var_words, qty_words = [], [], [], []

        for w in row:
            x = w['x']
            if x < sku_x - 10:
                nama_words.append(w['text'])
            elif x < variasi_x - 10:
                sku_words.append(w['text'])
            elif x < qty_x - 10:
                var_words.append(w['text'])
            else:
                qty_words.append(w['text'])

        nama    = re.sub(r'^\d+\s*', '', " ".join(nama_words)).strip()
        sku     = " ".join(sku_words).strip()
        variasi = " ".join(var_words).strip()
        qty_str = " ".join(qty_words).strip()

        if not sku:
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
    """Return (text, items) — sama seperti extract_text di ocr.py"""
    images = convert_from_path(pdf_path, dpi=300)
    full_text = ""
    all_items = []

    for img_pil in images:
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)

        # Resi dari barcode
        resi = extract_resi_from_barcode_cv(img_cv)

        # OCR untuk order_id dll
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
        ocr_text = pytesseract.image_to_string(thresh)

        if resi:
            full_text += f"No.Resi: {resi}\n" + ocr_text + "\n"
        else:
            full_text += ocr_text + "\n"

        # Parse tabel produk
        page_items = extract_items_from_img_cv(img_cv)
        all_items.extend(page_items)

    return full_text, all_items  # return tuple seperti ocr.py