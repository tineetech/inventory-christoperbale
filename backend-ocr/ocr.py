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
    table_offset_y = 0
    for start_pct in [0.55, 0.60, 0.65, 0.70]:
        y_start = int(height * start_pct)
        crop = gray[y_start:height, 0:width]
        _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        text = pytesseract.image_to_string(crop_bin, config="--psm 6")
        if re.search(r'nama\s*produk', text, re.IGNORECASE):
            table_gray = crop_bin
            table_offset_y = y_start
            break

    if table_gray is None:
        print("[WARN] Tabel tidak ditemukan")
        return []

    # Baca per-word dengan posisi x,y
    data = pytesseract.image_to_data(
        table_gray,
        output_type=pytesseract.Output.DICT,
        config="--psm 6"
    )

    # === Step 1: Temukan baris header dan posisi x tiap kolom ===
    # Kumpulkan semua word per baris (group by block_num + line_num)
    rows = {}
    for i, word in enumerate(data['text']):
        if not word.strip():
            continue
        key = (data['block_num'][i], data['line_num'][i])
        if key not in rows:
            rows[key] = []
        rows[key].append({
            'text': word,
            'x'   : data['left'][i],
            'y'   : data['top'][i],
            'w'   : data['width'][i],
            'conf': data['conf'][i],
        })

    # Cari baris header
    header_row = None
    header_key = None
    for key, words in rows.items():
        line_text = " ".join(w['text'] for w in words)
        if re.search(r'nama.{0,5}produk', line_text, re.IGNORECASE):
            header_row = words
            header_key = key
            break

    if not header_row:
        print("[WARN] Baris header tidak ditemukan")
        return []

    # === Step 2: Petakan x-position tiap kolom header ===
    # Cari x center dari kata SKU, Variasi, Qty di header
    col_x = {}
    header_text_full = " ".join(w['text'] for w in header_row)
    print(f"Header row: {header_text_full}")

    for w in header_row:
        t = w['text'].lower().strip('#').strip()
        if t in ('sku',):
            col_x['sku'] = w['x']
        elif t in ('variasi', 'varian'):
            col_x['variasi'] = w['x']
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x']

    print(f"Column x positions: {col_x}")

    if not col_x.get('sku'):
        print("[WARN] Kolom SKU tidak ditemukan di header")
        return []

    # === Step 3: Parse baris produk berdasarkan posisi x ===
    items = []
    header_y = header_row[0]['y']

    # Sort rows by y position
    sorted_rows = sorted(rows.items(), key=lambda kv: kv[1][0]['y'])

    for key, words in sorted_rows:
        # Skip header dan baris di atas header
        if words[0]['y'] <= header_y:
            continue

        line_text = " ".join(w['text'] for w in words)

        # Stop di baris Pesan:
        if re.match(r'Pesan\s*:', line_text, re.IGNORECASE):
            break

        # Harus diawali nomor urut
        if not re.match(r'^\d+', line_text):
            continue

        # Pisahkan kata berdasarkan posisi x relatif terhadap kolom
        sku_x     = col_x.get('sku', width * 0.5)
        variasi_x = col_x.get('variasi', width * 0.65)
        qty_x     = col_x.get('qty', width * 0.85)

        nama_words    = []
        sku_words     = []
        variasi_words = []
        qty_words     = []

        for w in words:
            x = w['x']
            if x < sku_x - 10:
                nama_words.append(w['text'])
            elif x < variasi_x - 10:
                sku_words.append(w['text'])
            elif x < qty_x - 10:
                variasi_words.append(w['text'])
            else:
                qty_words.append(w['text'])

        nama    = " ".join(nama_words).strip()
        sku     = " ".join(sku_words).strip()
        variasi = " ".join(variasi_words).strip()
        qty_str = " ".join(qty_words).strip()

        # Hapus nomor urut dari nama
        nama = re.sub(r'^\d+\s*', '', nama).strip()

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