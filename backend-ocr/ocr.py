from PIL import Image
import pytesseract
import cv2
import numpy as np
import re

def extract_resi_from_barcode(image_path):
    img = cv2.imread(image_path)
    
    # === Coba QR Code dulu (opencv built-in, no DLL needed) ===
    qr_detector = cv2.QRCodeDetector()
    data, _, _ = qr_detector.detectAndDecode(img)
    if data:
        print(f"QR detected: {repr(data)}")
        if re.search(r'SPX', data, re.IGNORECASE):
            return data.strip()

    # === Barcode (butuh opencv-contrib) ===
    try:
        barcode_detector = cv2.barcode.BarcodeDetector()
        ok, decoded_info, decoded_type, _ = barcode_detector.detectAndDecodeWithType(img)
        if ok:
            for info, btype in zip(decoded_info, decoded_type):
                print(f"Barcode detected: type={btype} data={repr(info)}")
                if re.search(r'SPX', info, re.IGNORECASE):
                    return info.strip()
    except AttributeError:
        print("cv2.barcode tidak tersedia, install opencv-contrib-python")

    # === Fallback: coba crop area barcode atas ===
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    height, width = gray.shape
    barcode_crop = gray[int(height*0.15):int(height*0.40), 0:width]
    barcode_crop = cv2.resize(barcode_crop, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
    
    try:
        barcode_detector = cv2.barcode.BarcodeDetector()
        ok, decoded_info, decoded_type, _ = barcode_detector.detectAndDecodeWithType(barcode_crop)
        if ok:
            for info in decoded_info:
                print(f"Barcode crop: {repr(info)}")
                if re.search(r'SPX', info, re.IGNORECASE):
                    return info.strip()
    except AttributeError:
        pass

    return None


def extract_shopee_resi(image_path):

    resi = extract_resi_from_barcode(image_path)
    if resi:
        print('metode scan utk resi digunakan')
        return resi

    img = cv2.imread(image_path)

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)

    data = pytesseract.image_to_data(gray, output_type=pytesseract.Output.DICT)

    resi_text = ""

    for i, word in enumerate(data['text']):
        if "resi" in word.lower():

            x = data['left'][i]
            y = data['top'][i]
            w = data['width'][i]
            h = data['height'][i]

            # ambil area kanan dari "Resi"
            crop = gray[y:y+h, x:x+800]

            text = pytesseract.image_to_string(crop, config="--psm 7")

            resi_text += text

    print('metode ocr cari kata kunci SPXID digunakan')
    return resi_text

def extract_shopee_order(image_path):

    img = cv2.imread(image_path)

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)

    data = pytesseract.image_to_data(gray, output_type=pytesseract.Output.DICT)

    order_text = ""

    for i, word in enumerate(data['text']):
        if "pesanan" in word.lower():

            x = data['left'][i]
            y = data['top'][i]
            w = data['width'][i]
            h = data['height'][i]

            # crop = gray[y:y+80, x:x+500]
            # crop = gray[y-20:y+120, x:x+900]
            crop = gray[y-100:y+300, x:x+1200]

            text = pytesseract.image_to_string(crop, config="--psm 6")

            order_text += text

    return order_text

def extract_shopee_items(image_path):
    img = cv2.imread(image_path)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
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
        print("[WARN] Tabel tidak ditemukan")
        return []

    data = pytesseract.image_to_data(
        table_gray,
        output_type=pytesseract.Output.DICT,
        config="--psm 6"
    )

    # === Group by Y position (cluster kata yang Y-nya berdekatan) ===
    # Lebih reliable daripada block_num/line_num untuk tabel
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

    # Cluster berdasarkan Y: kata dengan Y berdekatan (±15px) = 1 baris
    words_list.sort(key=lambda w: w['y'])
    
    rows = []
    current_row = [words_list[0]]
    
    for w in words_list[1:]:
        # Bandingkan dengan rata-rata Y baris saat ini
        avg_y = sum(r['y'] for r in current_row) / len(current_row)
        if abs(w['y'] - avg_y) <= 20:  # threshold 20px
            current_row.append(w)
        else:
            # Sort kata dalam baris berdasarkan X
            current_row.sort(key=lambda r: r['x'])
            rows.append(current_row)
            current_row = [w]
    
    if current_row:
        current_row.sort(key=lambda r: r['x'])
        rows.append(current_row)

    # Debug: print semua baris
    print("=== All rows ===")
    for r in rows:
        print(f"  y={r[0]['y']:4d} | {' '.join(w['text'] for w in r)}")

    # === Cari baris header ===
    header_row = None
    for row in rows:
        line_text = " ".join(w['text'] for w in row)
        if re.search(r'nama.{0,5}produk', line_text, re.IGNORECASE):
            header_row = row
            break

    if not header_row:
        print("[WARN] Header tidak ditemukan")
        return []

    print(f"Header: {' '.join(w['text'] for w in header_row)}")

    # === Petakan posisi X kolom dari header ===
    col_x = {}
    for w in header_row:
        t = w['text'].lower().strip('#').strip()
        if t == 'sku':
            col_x['sku'] = w['x']
        elif t in ('variasi', 'varian'):
            col_x['variasi'] = w['x']
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x']

    print(f"Column X: {col_x}")

    if not col_x.get('sku'):
        print("[WARN] Kolom SKU tidak ditemukan")
        return []

    sku_x     = col_x['sku']
    variasi_x = col_x.get('variasi', width * 0.65)
    qty_x     = col_x.get('qty', width * 0.85)

    # === Parse baris produk ===
    items = []
    header_y = header_row[0]['y']

    for row in rows:
        if row[0]['y'] <= header_y:
            continue

        line_text = " ".join(w['text'] for w in row)

        if re.match(r'Pesan\s*:', line_text, re.IGNORECASE):
            break

        # Harus diawali nomor urut
        if not re.match(r'^\d+', line_text):
            continue

        nama_words = []
        sku_words  = []
        var_words  = []
        qty_words  = []

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
            print(f"[SKIP no sku] {repr(line_text)}")
            continue

        try:
            qty = int(re.sub(r'\D', '', qty_str)) if qty_str else 1
        except ValueError:
            qty = 1

        print(f"[ITEM] nama={repr(nama)} sku={repr(sku)} variasi={repr(variasi)} qty={qty}")
        items.append({
            "nama"   : nama,
            "sku"    : sku,
            "variasi": variasi,
            "qty"    : qty,
        })

    return items
    
def extract_text(image_path):
    resi_text  = extract_shopee_resi(image_path)
    order_text = extract_shopee_order(image_path)
    items      = extract_shopee_items(image_path)  # ini list, bukan string

    # Text untuk parser resi & order_id
    text = resi_text + "\n" + order_text

    print(text)

    # Return tuple: (text, items)
    return text, items