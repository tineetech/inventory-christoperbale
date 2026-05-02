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

    # ----------------------------------------------------------------
    # Kelompokkan rows menjadi "item groups"
    # Item baru: line_text diawali angka (nomor urut) diikuti spasi + huruf
    # Toleran terhadap OCR noise (misal '1Christian' → tetap match r'^\d+\s*\S')
    # Baris wrap: tidak diawali angka, atau angka tapi bukan nomor urut item
    # ----------------------------------------------------------------
    item_groups: list[list] = []   # tiap elemen = list of rows (1 item)

    for row in rows:
        if row[0]['y'] <= header_y:
            continue

        line_text = " ".join(w['text'] for w in row)

        # Sentinel: baris "Pesan:" → stop
        if re.match(r'Pesan\s*:', line_text, re.IGNORECASE):
            break

        # Deteksi item baru: line_text diawali angka kecil (1-99) lalu spasi/huruf
        # AND word paling kiri ada di zona kolom nama (bukan zona SKU/variasi/qty)
        leftmost = min(row, key=lambda w: w['x'])
        line_starts_with_num = bool(re.match(r'^\d{1,2}[\s\.]', line_text))
        leftmost_in_nama_zone = leftmost['x'] < sku_x

        # Fallback: kalau line_text diawali angka tapi tanpa spasi (OCR noise)
        # cek apakah word pertama murni angka kecil
        if not line_starts_with_num:
            first_word = row[0]['text'] if row else ''
            line_starts_with_num = bool(re.match(r'^\d{1,2}$', first_word))

        is_new_item = line_starts_with_num and leftmost_in_nama_zone

        print(f"[ROW] y={row[0]['y']} is_new={is_new_item} text={repr(line_text[:60])}")

        if is_new_item:
            item_groups.append([row])
        elif item_groups:
            # Baris lanjutan (wrap nama produk / kolom lain)
            item_groups[-1].append(row)
        # Kalau belum ada grup, skip (sisa header dll)

    # ----------------------------------------------------------------
    # Parse tiap grup → 1 item
    # ----------------------------------------------------------------
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

        # Buang nomor urut dari nama
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