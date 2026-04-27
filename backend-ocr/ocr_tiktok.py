import pytesseract
import cv2
import numpy as np
import re
from pdf2image import convert_from_path


def extract_resi_from_barcode_cv(img_cv):
    """Coba baca No. Resi JX dari QR/barcode."""
    qr_detector = cv2.QRCodeDetector()
    data, _, _ = qr_detector.detectAndDecode(img_cv)
    if data and re.search(r'JX\d{8,}', data, re.IGNORECASE):
        print(f"[QR TikTok] {repr(data)}")
        return data.strip()

    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, decoded_info, _, _ = bd.detectAndDecodeWithType(img_cv)
        if ok:
            for info in decoded_info:
                if info and re.search(r'JX\d{8,}', info):
                    print(f"[Barcode TikTok] {repr(info)}")
                    return info.strip()

        # Crop area barcode tengah (J&T barcode biasanya di 50-75% height)
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        h, w = gray.shape
        for y1, y2 in [(0.50, 0.80), (0.30, 0.65)]:
            crop = gray[int(h*y1):int(h*y2), 0:w]
            crop = cv2.resize(crop, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
            _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
            crop_bgr = cv2.cvtColor(crop_bin, cv2.COLOR_GRAY2BGR)
            ok2, info2, _, _ = bd.detectAndDecodeWithType(crop_bgr)
            if ok2:
                for info in info2:
                    if info and re.search(r'JX\d{8,}', info):
                        print(f"[Barcode crop TikTok] {repr(info)}")
                        return info.strip()
    except AttributeError:
        pass

    return None


def extract_tiktok_items_from_img(img_cv):
    """Parse tabel Product Name | SKU | Seller SKU | Qty dari label TikTok J&T."""
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    height, width = gray.shape

    # Tabel produk di bagian bawah label (~70-100%)
    table_gray = None
    for start_pct in [0.65, 0.70, 0.75, 0.80]:
        y_start = int(height * start_pct)
        crop = gray[y_start:height, 0:width]
        _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        text = pytesseract.image_to_string(crop_bin, config="--psm 6")
        if re.search(r'product\s*name|seller\s*sku', text, re.IGNORECASE):
            table_gray = crop_bin
            print(f"[TikTok] Tabel ditemukan di {start_pct}")
            break

    if table_gray is None:
        print("[WARN TikTok] Tabel tidak ditemukan")
        return []

    data = pytesseract.image_to_data(
        table_gray,
        output_type=pytesseract.Output.DICT,
        config="--psm 6"
    )

    # Cluster words by Y
    words_list = []
    for i, word in enumerate(data['text']):
        if not word.strip() or data['conf'][i] < 0:
            continue
        words_list.append({
            'text': word,
            'x': data['left'][i],
            'y': data['top'][i],
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

    print("=== TikTok rows ===")
    for r in rows:
        print(f"  y={r[0]['y']:4d} | {' '.join(w['text'] for w in r)}")

    # Cari header: "Product Name SKU Seller SKU Qty"
    header_row = None
    for row in rows:
        line = " ".join(w['text'] for w in row)
        if re.search(r'product.{0,5}name', line, re.IGNORECASE):
            header_row = row
            break

    if not header_row:
        print("[WARN TikTok] Header tidak ditemukan")
        return []

    print(f"[TikTok] Header: {' '.join(w['text'] for w in header_row)}")

    # Petakan posisi kolom
    col_x = {}
    for w in header_row:
        t = w['text'].lower().strip()
        if t == 'sku':
            # Kolom SKU pertama = variasi, Seller SKU = yang kita mau
            if 'sku' not in col_x:
                col_x['sku'] = w['x']
        elif t == 'seller':
            col_x['seller_sku'] = w['x']
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x']

    print(f"[TikTok] Column X: {col_x}")

    if not col_x.get('seller_sku'):
        print("[WARN TikTok] Kolom Seller SKU tidak ditemukan")
        return []

    sku_x        = col_x.get('sku', width * 0.40)
    seller_sku_x = col_x['seller_sku']
    qty_x        = col_x.get('qty', width * 0.85)
    header_y     = header_row[0]['y']

    items = []
    # Kumpulkan baris produk — bisa multi-baris untuk 1 produk
    # Baris produk: tidak diawali angka urut, tapi ada konten
    # Stop di "Qty Total:"
    product_rows = []
    for row in rows:
        if row[0]['y'] <= header_y:
            continue
        line = " ".join(w['text'] for w in row)
        if re.search(r'qty\s*total', line, re.IGNORECASE):
            break
        product_rows.append(row)

    # Gabungkan baris yang tidak punya seller_sku ke baris sebelumnya (wrap nama produk)
    items = []
    for row in product_rows:
        line = " ".join(w['text'] for w in row)

        # Cek apakah baris ini punya kata di kolom seller_sku
        seller_sku_words = [w for w in row if w['x'] >= seller_sku_x - 20]
        
        if not seller_sku_words:
            # Baris ini hanya lanjutan nama produk (wrap) — skip
            # Nama sudah cukup dari baris utama
            continue

        nama_words = []
        sku_words  = []
        s_sku_words = []
        qty_words  = []

        for w in row:
            x = w['x']
            if x < sku_x - 10:
                nama_words.append(w['text'])
            elif x < seller_sku_x - 10:
                sku_words.append(w['text'])
            elif x < qty_x - 10:
                s_sku_words.append(w['text'])
            else:
                qty_words.append(w['text'])

        nama       = " ".join(nama_words).strip()
        variasi    = " ".join(sku_words).strip()
        seller_sku = " ".join(s_sku_words).strip()
        qty_str    = " ".join(qty_words).strip()

        if not seller_sku:
            continue

        try:
            qty = int(re.sub(r'\D', '', qty_str)) if qty_str else 1
        except ValueError:
            qty = 1

        print(f"[TikTok ITEM] nama={repr(nama)} variasi={repr(variasi)} seller_sku={repr(seller_sku)} qty={qty}")
        items.append({
            "nama"   : nama,
            "sku"    : seller_sku,
            "variasi": variasi,
            "qty"    : qty,
        })

    return items

    for row in items:
        nama_words       = []
        sku_words        = []
        seller_sku_words = []
        qty_words        = []

        for w in row:
            x = w['x']
            if x < sku_x - 10:
                nama_words.append(w['text'])
            elif x < seller_sku_x - 10:
                sku_words.append(w['text'])
            elif x < qty_x - 10:
                seller_sku_words.append(w['text'])
            else:
                qty_words.append(w['text'])

        nama       = " ".join(nama_words).strip()
        variasi    = " ".join(sku_words).strip()      # kolom SKU = variasi di TikTok
        seller_sku = " ".join(seller_sku_words).strip()
        qty_str    = " ".join(qty_words).strip()

        if not seller_sku:
            continue

        try:
            qty = int(re.sub(r'\D', '', qty_str)) if qty_str else 1
        except ValueError:
            qty = 1

        print(f"[TikTok ITEM] nama={repr(nama)} variasi={repr(variasi)} seller_sku={repr(seller_sku)} qty={qty}")
        items.append({
            "nama"      : nama,
            "sku"       : seller_sku,   # Seller SKU = yang dipakai sebagai SKU
            "variasi"   : variasi,
            "qty"       : qty,
        })

    return items


def extract_tiktok_from_pdf(pdf_path):
    """Return (text, items) untuk TikTok J&T PDF."""
    images = convert_from_path(pdf_path)
    full_text = ""
    all_items = []

    for img_pil in images:
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)

        # Resi dari barcode
        resi = extract_resi_from_barcode_cv(img_cv)

        # OCR full untuk order_id
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
        ocr_text = pytesseract.image_to_string(thresh)

        if resi:
            full_text += f"Resi: {resi}\n" + ocr_text + "\n"
        else:
            full_text += ocr_text + "\n"

        page_items = extract_tiktok_items_from_img(img_cv)
        all_items.extend(page_items)

    return full_text, all_items


def extract_tiktok_from_image(image_path):
    """Return (text, items) untuk TikTok J&T image."""
    img_cv = cv2.imread(image_path)

    resi = extract_resi_from_barcode_cv(img_cv)

    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    gray_2x = cv2.resize(gray, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    thresh = cv2.threshold(gray_2x, 150, 255, cv2.THRESH_BINARY)[1]
    ocr_text = pytesseract.image_to_string(thresh)

    if resi:
        text = f"Resi: {resi}\n" + ocr_text
    else:
        text = ocr_text

    items = extract_tiktok_items_from_img(img_cv)
    return text, items