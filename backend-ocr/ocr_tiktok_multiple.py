"""
ocr_tiktok_multiple.py  (v3 - fixed order_id + seller_sku)
===========================================================
Fix v3:
  1. Order ID: perluas zona OCR ke 68-90%, tambah multiple crop zone,
     dan fallback baca langsung dari teks OCR full halaman bagian bawah.
     Pattern "Order Id : 583..." ada tepat di bawah barcode 1D.

  2. Seller SKU: perbaikan boundary kolom menggunakan posisi AKTUAL
     kata "Seller" di header, bukan midpoint — karena "SKU" pertama
     (variasi) dan "Seller SKU" jaraknya tidak proporsional.
     
     Dari label asli (PDF rendered):
       - Kolom SKU (variasi): ~40% width  
       - Kolom Seller SKU:    ~60% width
       - Kolom Qty:           ~85% width
     
     Fix: setelah dapat x dari header kata "Seller", boundary kanan
     kolom SKU/variasi = midpoint(sku_x, seller_x) - tapi dengan
     toleransi lebih lebar (+30px) supaya "39", "Hitam," tidak nyasar
     ke kolom Seller SKU.

  3. Multi-baris Seller SKU (kasus Burgundy, 38 terpotong):
     Jika baris berikutnya hanya berisi angka di zona SKU dan tidak
     punya Seller SKU → angka itu adalah lanjutan variasi baris sebelumnya.

Layout resi TikTok J&T (portrait):
  ┌─────────────────────────────────────┐
  │  Header J&T (logo, pengirim, dst)   │  0-45%
  ├─────────────────────────────────────┤
  │  Nama toko besar (bold)             │  45-55%
  │  Kode toko (856-TTE08-01, dll)      │
  ├─────────────────────────────────────┤
  │  [BARCODE 1D besar - No. Resi JX]   │  55-75%
  │      JX9214131965                   │
  ├──────────────────────┬──────────────┤
  │ Order Id: 58369...   │ Estimated    │  75-80%  <- ORDER ID DI SINI
  ├──────────────────────┴──────────────┤
  │  Tabel: Product Name | SKU |        │  80-100%
  │         Seller SKU   | Qty          │
  └─────────────────────────────────────┘
"""

import re
import cv2
import numpy as np
import pytesseract
import base64
import traceback
from pdf2image import convert_from_path

from parser.parser_tiktok import parse_tiktok


# ──────────────────────────────────────────────────────────────────
# Helper: encode cv2 image ke base64 JPEG
# ──────────────────────────────────────────────────────────────────

def _cv_to_base64_jpeg(img_cv: np.ndarray, quality: int = 70, max_width: int = 900) -> str:
    try:
        h, w = img_cv.shape[:2]
        if w > max_width:
            scale  = max_width / w
            img_cv = cv2.resize(img_cv, (max_width, int(h * scale)),
                                interpolation=cv2.INTER_AREA)
        ok, buf = cv2.imencode('.jpg', img_cv, [cv2.IMWRITE_JPEG_QUALITY, quality])
        if not ok:
            print("[base64] cv2.imencode gagal")
            return ''
        result = base64.b64encode(buf.tobytes()).decode('utf-8')
        print(f"[base64] encoded len={len(result)}")
        return result
    except Exception as e:
        print(f"[base64] Exception: {e}")
        traceback.print_exc()
        return ''


# ──────────────────────────────────────────────────────────────────
# Baca No. Resi JX dari barcode 1D besar di tengah resi
# ──────────────────────────────────────────────────────────────────

def _extract_resi_from_barcode(img_cv: np.ndarray) -> str | None:
    JX_RE = re.compile(r'JX\d{10}', re.IGNORECASE)

    # --- Strategy 1: cv2 QRCodeDetector ---
    qr = cv2.QRCodeDetector()
    data, _, _ = qr.detectAndDecode(img_cv)
    if data:
        m = JX_RE.search(data)
        if m:
            print(f"[Resi JX] QR: {m.group()}")
            return m.group()

    # --- Strategy 2: cv2 BarcodeDetector full image ---
    bd = None
    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, infos, _, _ = bd.detectAndDecodeWithType(img_cv)
        if ok:
            for info in infos:
                if info:
                    m = JX_RE.search(info)
                    if m:
                        print(f"[Resi JX] BarcodeDetector full: {m.group()}")
                        return m.group()
    except AttributeError:
        bd = None

    # --- Strategy 3: crop zona barcode JX (40-82% tinggi) + scale 3x ---
    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    if bd is not None:
        for (y0p, y1p) in [(0.50, 0.80), (0.45, 0.82), (0.40, 0.85)]:
            y0   = int(h * y0p)
            y1   = int(h * y1p)
            crop = gray[y0:y1, 0:w]
            if crop.size == 0:
                continue
            scaled = cv2.resize(crop, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
            _, bw  = cv2.threshold(scaled, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
            crop_bgr = cv2.cvtColor(bw, cv2.COLOR_GRAY2BGR)
            try:
                ok2, infos2, _, _ = bd.detectAndDecodeWithType(crop_bgr)
                if ok2:
                    for info in infos2:
                        if info:
                            m = JX_RE.search(info)
                            if m:
                                print(f"[Resi JX] BarcodeDetector crop ({y0p},{y1p}): {m.group()}")
                                return m.group()
            except Exception:
                pass

    # --- Strategy 4: zxing-cpp fallback ---
    try:
        import zxingcpp
        for (y0p, y1p) in [(0.50, 0.80), (0.40, 0.85), (0.30, 0.90)]:
            y0   = int(h * y0p)
            y1   = int(h * y1p)
            crop = gray[y0:y1, 0:w]
            if crop.size == 0:
                continue
            for scale in [3, 5, 1]:
                if scale == 1:
                    img_scan = crop
                else:
                    img_scan = cv2.resize(crop, None, fx=scale, fy=scale,
                                          interpolation=cv2.INTER_CUBIC)
                    _, img_scan = cv2.threshold(img_scan, 0, 255,
                                                cv2.THRESH_BINARY + cv2.THRESH_OTSU)
                for r in zxingcpp.read_barcodes(img_scan):
                    m = JX_RE.search(r.text)
                    if m:
                        print(f"[Resi JX] zxing ({y0p},{y1p}) {scale}x: {m.group()}")
                        return m.group()
    except ImportError:
        pass

    print("[Resi JX] Tidak ditemukan dari barcode, fallback ke OCR teks")
    return None


# ──────────────────────────────────────────────────────────────────
# FIX v3: Baca Order ID - zona diperluas + multiple crop + fallback
# ──────────────────────────────────────────────────────────────────

def _extract_order_id_from_ocr(img_cv: np.ndarray) -> str | None:
    """
    Baca Order ID dari teks OCR di zona bawah barcode resi TikTok.

    "Order Id : 583699161565201746" ada di ~75-82% tinggi halaman,
    di sisi KIRI, bersebrangan dengan "Estimated Date:" di sisi kanan.

    Fix v3:
    - Coba beberapa zona crop yang lebih luas (68-92%)
    - Juga coba crop hanya sisi KIRI (0-55% width) supaya teks
      "Order Id" tidak tercampur "Estimated Date"
    - Fallback: OCR full halaman bagian bawah (65-100%)
    """
    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    ORDER_RE = re.compile(
        r'Order\s*I[dD]\s*[:\s]*(\d{12,})',
        re.IGNORECASE
    )
    LONG_NUM_RE = re.compile(r'\b(\d{15,})\b')

    def _ocr_crop(y0_pct, y1_pct, x0_pct=0.0, x1_pct=1.0, scale=3):
        y0 = int(h * y0_pct)
        y1 = int(h * y1_pct)
        x0 = int(w * x0_pct)
        x1 = int(w * x1_pct)
        crop = gray[y0:y1, x0:x1]
        if crop.size == 0:
            return ""
        scaled = cv2.resize(crop, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
        _, bw  = cv2.threshold(scaled, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        return pytesseract.image_to_string(bw, config="--psm 6")

    # Daftar zona yang dicoba, dari yang paling spesifik ke paling luas
    # (y0_pct, y1_pct, x0_pct, x1_pct)
    zones = [
        (0.65, 0.90, 0.0, 1.00),   # ← pindah ke atas, ini yang selalu berhasil
        (0.72, 0.83, 0.0, 0.55),
        (0.70, 0.85, 0.0, 0.55),
        (0.68, 0.87, 0.0, 0.60),
        (0.60, 1.00, 0.0, 1.00),   # fallback
    ]

    for (y0p, y1p, x0p, x1p) in zones:
        text = _ocr_crop(y0p, y1p, x0p, x1p)
        raw  = text.strip()[:150]
        print(f"[OrderID OCR zona {y0p:.2f}-{y1p:.2f} x{x0p:.1f}-{x1p:.1f}] raw='{raw}'")

        m = ORDER_RE.search(text)
        if m:
            order_id = m.group(1)
            print(f"[OrderID OCR zona] found: {order_id}")
            return order_id

        # Fallback angka panjang
        nums = LONG_NUM_RE.findall(text)
        if nums:
            order_id = nums[0]
            print(f"[OrderID OCR zona] fallback angka panjang: {order_id}")
            return order_id

    print("[OrderID OCR zona] Tidak ditemukan")
    return None


# ──────────────────────────────────────────────────────────────────
# FIX v3: Extract items - perbaikan boundary kolom Seller SKU
# ──────────────────────────────────────────────────────────────────

def _extract_items_tiktok(img_cv: np.ndarray) -> list[dict]:
    """
    Parse tabel produk di bagian bawah resi TikTok.
    Kolom: Product Name | SKU (variasi) | Seller SKU | Qty

    Fix v3 boundary:
    - Dari observasi label asli, kolom di PDF ~A4 portrait:
        Product Name : x=0    ~ 38% width
        SKU          : x=38%  ~ 52% width  (kolom variasi: "Tan, 39", "Hitam, 38")
        Seller SKU   : x=58%  ~ 83% width  (CBJESSICA, CBJULIE - bisa kosong)
        Qty          : x=83%  ~ 100% width
    - Boundary dihitung dari posisi HEADER WORD, bukan midpoint murni.
    - Toleransi: b_sku_end = seller_sku_x - 20 (bukan midpoint)
      supaya variasi multi-token ("Bronze, 41") tidak overflowing ke Seller SKU.
    
    Fix multi-baris variasi (Burgundy case):
    - Jika baris lanjutan hanya ada kata di zona SKU/variasi (dan tidak ada 
      di zona nama maupun Seller SKU), gabungkan ke variasi item sebelumnya.

    Multi-item per resi:
    - Setiap ketemu Seller SKU valid baru = item baru, item sebelumnya disimpan.
    - Qty dibaca dari kolom Qty di baris yang sama dengan Seller SKU.
    - Jika qty tidak ada di baris utama (qty_line kosong), cari di baris lanjutan
      yang punya kata di zona qty saja.
    - Default qty = 1 jika sama sekali tidak terbaca.
    """
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    height, width = gray.shape

    # Cari area tabel
    table_gray     = None
    table_y_offset = 0
    for start_pct in [0.70, 0.72, 0.75, 0.78, 0.80, 0.82]:
        y_start = int(height * start_pct)
        crop    = gray[y_start:height, 0:width]
        _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        text = pytesseract.image_to_string(crop_bin, config="--psm 6")
        if re.search(r'product\s*name|seller\s*sku', text, re.IGNORECASE):
            table_gray     = crop_bin
            table_y_offset = y_start
            print(f"[TikTok Items] Tabel ditemukan di {start_pct:.0%}")
            break

    if table_gray is None:
        print("[TikTok Items] Tabel tidak ditemukan")
        return []

    # OCR word-level
    data = pytesseract.image_to_data(
        table_gray,
        output_type=pytesseract.Output.DICT,
        config="--psm 6"
    )

    # Cluster words per baris (toleransi Y = 20px)
    words_list = []
    for i, word in enumerate(data['text']):
        if not word.strip() or data['conf'][i] < 0:
            continue
        words_list.append({
            'text': word,
            'x'   : data['left'][i],
            'y'   : data['top'][i],
        })

    if not words_list:
        return []

    words_list.sort(key=lambda w: w['y'])
    rows, cur = [], [words_list[0]]
    for w in words_list[1:]:
        avg_y = sum(r['y'] for r in cur) / len(cur)
        if abs(w['y'] - avg_y) <= 20:
            cur.append(w)
        else:
            cur.sort(key=lambda r: r['x'])
            rows.append(cur)
            cur = [w]
    if cur:
        cur.sort(key=lambda r: r['x'])
        rows.append(cur)

    print(f"[TikTok Items] Total rows OCR: {len(rows)}")
    for row in rows[:8]:
        print(f"  y={row[0]['y']:4d} | {' '.join(w['text'] for w in row)}")

    # Cari header row
    header_row = None
    for row in rows:
        line = " ".join(w['text'] for w in row)
        if re.search(r'product\s*name', line, re.IGNORECASE):
            header_row = row
            print(f"[TikTok Items] Header: {line}")
            break

    if not header_row:
        print("[TikTok Items] Header tidak ditemukan")
        return []

    # ── Deteksi posisi kolom dari header ──────────────────────────
    col_x      = {}
    saw_seller = False
    seller_x_candidate = None

    for w in header_row:
        t = w['text'].lower().strip()
        if t == 'seller':
            saw_seller = True
            seller_x_candidate = w['x']
        elif t == 'sku':
            if saw_seller and seller_x_candidate is not None:
                col_x['seller_sku'] = seller_x_candidate
                saw_seller = False
                seller_x_candidate = None
            else:
                if 'sku' not in col_x:
                    col_x['sku'] = w['x']
                saw_seller = False
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x']
            saw_seller = False
            seller_x_candidate = None
        elif t not in ('name', 'product'):
            saw_seller = False
            seller_x_candidate = None

    print(f"[TikTok Items] Column X raw: {col_x}")

    if 'seller_sku' not in col_x:
        print("[TikTok Items] Kolom Seller SKU tidak ditemukan, skip items")
        return []

    sku_x        = col_x.get('sku',        width * 0.35)
    seller_sku_x = col_x['seller_sku']
    qty_x        = col_x.get('qty',        width * 0.88)

    # ── FIX v3: Boundary kolom ──────────────────────────────────────
    # b_nama_end: batas kanan kolom Product Name = posisi kolom SKU
    # b_sku_end : batas kanan kolom SKU/variasi  = seller_sku_x - 20px
    #             (bukan midpoint - supaya "39", "Hitam," tidak overflow)
    # b_seller_end: batas kanan Seller SKU = midpoint(seller_sku_x, qty_x)
    b_nama_end   = sku_x
    b_sku_end    = seller_sku_x - 20          # FIX: ketat ke kiri dari Seller SKU
    b_seller_end = (seller_sku_x + qty_x) / 2

    print(f"[TikTok Items] Boundaries: nama<{b_nama_end:.0f} "
          f"| sku<{b_sku_end:.0f} | seller_sku<{b_seller_end:.0f} | qty≥{b_seller_end:.0f}")

    header_y = header_row[0]['y']

    # ── Parse baris produk ────────────────────────────────────────
    items = []
    current_item = None

    for row in rows:
        if row[0]['y'] <= header_y:
            continue

        line = " ".join(w['text'] for w in row)

        if re.search(r'qty\s*total|order\s*i[dD]', line, re.IGNORECASE):
            break

        nama_parts       = []
        sku_parts        = []
        seller_sku_parts = []
        qty_parts        = []

        for w in row:
            x = w['x']
            if x < b_nama_end:
                nama_parts.append(w['text'])
            elif x < b_sku_end:
                sku_parts.append(w['text'])
            elif x < b_seller_end:
                seller_sku_parts.append(w['text'])
            else:
                qty_parts.append(w['text'])

        seller_sku_raw = " ".join(seller_sku_parts).strip()
        nama_line      = " ".join(nama_parts).strip()
        sku_line       = " ".join(sku_parts).strip()
        qty_line       = " ".join(qty_parts).strip()

        print(f"[TikTok Items] y={row[0]['y']:4d} | "
              f"nama={repr(nama_line[:30])} | "
              f"sku_var={repr(sku_line[:15])} | "
              f"seller_sku={repr(seller_sku_raw)} | "
              f"qty={repr(qty_line)}")

        # Seller SKU valid: hanya huruf/angka/dash/underscore, min 2 char
        seller_sku_valid = (
            seller_sku_raw
            and re.match(r'^[A-Za-z0-9\-_]{2,}$', seller_sku_raw)
        )

        if seller_sku_valid:
            # ── Baris utama item BARU ──────────────────────────────
            if current_item:
                items.append(current_item)

            # Qty: ambil dari baris ini; strip semua non-digit lalu parse
            qty = 1
            if qty_line:
                qty_digits = re.sub(r'\D', '', qty_line)
                try:
                    qty = int(qty_digits) if qty_digits else 1
                except ValueError:
                    qty = 1

            current_item = {
                "nama"    : nama_line,
                "sku"     : seller_sku_raw,
                "variasi" : sku_line,
                "qty"     : qty,
                "_qty_set": qty_line != "",   # flag: qty sudah terbaca di baris ini
            }

        elif current_item:
            # ── Baris lanjutan ──────────────────────────────────────
            # Klasifikasi berdasarkan kolom mana yang terisi:
            #
            # (a) Ada qty_line dan qty belum di-set → ambil qty dari baris ini
            #     Terjadi kalau layout qty turun ke baris 2 (jarang tapi mungkin)
            #
            # (b) Hanya sku_line terisi (Burgundy case: "38" di baris bawah)
            #     → lanjutan variasi
            #
            # (c) Ada nama_line → lanjutan nama produk (wrap)
            #     → gabungkan nama, dan sku_line juga jika ada

            if qty_line and not current_item.get("_qty_set"):
                qty_digits = re.sub(r'\D', '', qty_line)
                try:
                    current_item["qty"] = int(qty_digits) if qty_digits else current_item["qty"]
                    current_item["_qty_set"] = True
                except ValueError:
                    pass

            if sku_line and not nama_line and not seller_sku_raw:
                # Kasus Burgundy: hanya lanjutan variasi
                current_item["variasi"] = (current_item["variasi"] + " " + sku_line).strip()
            elif nama_line:
                # Wrap nama produk
                current_item["nama"] = (current_item["nama"] + " " + nama_line).strip()
                if sku_line:
                    current_item["variasi"] = (current_item["variasi"] + " " + sku_line).strip()

    if current_item:
        items.append(current_item)

    # Hapus internal flag sebelum return
    for item in items:
        item.pop("_qty_set", None)

    for item in items:
        print(f"[TikTok Items] FINAL: sku={repr(item['sku'])} "
              f"variasi={repr(item['variasi'])} qty={item['qty']} "
              f"nama={repr(item['nama'][:40])}")

    return items


# ──────────────────────────────────────────────────────────────────
# Proses 1 halaman
# ──────────────────────────────────────────────────────────────────
def _parse_items_tiktok_from_pdf_page(page) -> list[dict]:
    """Parse items dari PDF text layer TikTok menggunakan koordinat X kata."""
    try:
        words = page.extract_words(x_tolerance=3, y_tolerance=3,
                                   keep_blank_chars=False, use_text_flow=False)
    except Exception as e:
        print(f"[TikTok PDF Words] extract_words error: {e}")
        return []

    if not words:
        return []

    # Cluster per baris
    words.sort(key=lambda w: (w['top'], w['x0']))
    rows, cur = [], [words[0]]
    for w in words[1:]:
        avg_top = sum(r['top'] for r in cur) / len(cur)
        if abs(w['top'] - avg_top) <= 5:
            cur.append(w)
        else:
            cur.sort(key=lambda r: r['x0'])
            rows.append(cur)
            cur = [w]
    if cur:
        cur.sort(key=lambda r: r['x0'])
        rows.append(cur)

    # Cari header
    header_row = None
    header_idx = None
    for i, row in enumerate(rows):
        line = " ".join(w['text'] for w in row)
        if re.search(r'product\s*name', line, re.IGNORECASE):
            header_row = row
            header_idx = i
            break

    if not header_row:
        return []

    # Posisi kolom dari header
    page_width   = page.width
    col_x        = {}
    saw_seller   = False
    seller_x_cand = None

    for w in header_row:
        t = w['text'].lower().strip()
        if t == 'seller':
            saw_seller = True
            seller_x_cand = w['x0']
        elif t == 'sku':
            if saw_seller and seller_x_cand is not None:
                col_x['seller_sku'] = seller_x_cand
                saw_seller = False
            else:
                col_x.setdefault('sku', w['x0'])
                saw_seller = False
        elif t in ('qty', 'jumlah'):
            col_x['qty'] = w['x0']
            saw_seller = False
        elif t not in ('name', 'product'):
            saw_seller = False

    if 'seller_sku' not in col_x:
        return []

    x_sku        = col_x.get('sku',        page_width * 0.35)
    x_seller_sku = col_x['seller_sku']
    x_qty        = col_x.get('qty',        page_width * 0.85)
    b_seller_end = (x_seller_sku + x_qty) / 2

    items = []
    current_item = None

    for row in rows[header_idx + 1:]:
        line = " ".join(w['text'] for w in row)
        if re.search(r'qty\s*total|order\s*i[dD]', line, re.IGNORECASE):
            break

        nama_p, sku_p, seller_p, qty_p = [], [], [], []
        for w in row:
            x = w['x0']
            if x < x_sku:
                nama_p.append(w['text'])
            elif x < x_seller_sku - 5:
                sku_p.append(w['text'])
            elif x < b_seller_end:
                seller_p.append(w['text'])
            else:
                qty_p.append(w['text'])

        seller_raw = " ".join(seller_p).strip()
        nama_line  = " ".join(nama_p).strip()
        sku_line   = " ".join(sku_p).strip()
        qty_line   = " ".join(qty_p).strip()

        seller_valid = bool(seller_raw and re.match(r'^[A-Za-z0-9\-_]{2,}$', seller_raw))

        if seller_valid:
            if current_item:
                items.append(current_item)
            qty_digits = re.sub(r'\D', '', qty_line)
            current_item = {
                "nama"    : nama_line,
                "sku"     : seller_raw,
                "variasi" : sku_line,
                "qty"     : int(qty_digits) if qty_digits else 1,
            }
        elif current_item:
            if nama_line:
                current_item["nama"] = (current_item["nama"] + " " + nama_line).strip()
            if sku_line:
                current_item["variasi"] = (current_item["variasi"] + " " + sku_line).strip()

    if current_item:
        items.append(current_item)

    for item in items:
        print(f"[TikTok PDF Words] FINAL: sku={item['sku']} variasi={item['variasi']} qty={item['qty']}")

    return items
    
def _process_single_page(img_cv: np.ndarray, page_num: int, pdf_path: str = None,ekspedisi_mode: str = None) -> dict:
    print(f"\n[TikTok Page {page_num}] Mulai proses...")

    image_b64 = _cv_to_base64_jpeg(img_cv)
    print(f"[TikTok Page {page_num}] image_base64 length={len(image_b64)}")

    # 1. Resi dari barcode (selalu pakai cv2, cepat)
    resi = _extract_resi_from_barcode(img_cv)

    # ── Coba PDF text layer dulu (jauh lebih cepat dari OCR) ──────
    if pdf_path:
        try:
            import pdfplumber
            with pdfplumber.open(pdf_path) as pdf:
                page = pdf.pages[page_num - 1]
                text = page.extract_text() or ""
                if text.strip():
                    print(f"[TikTok Page {page_num}] ✅ PDF text layer OK, skip OCR")
                    m = re.search(r'Order\s*I[dD]\s*[:\s]+(\d{15,})', text)
                    order_id = m.group(1) if m else None
                    items    = _parse_items_tiktok_from_pdf_page(page)
                    if not resi:
                        m2 = re.search(r'JX\d{10}', text, re.IGNORECASE)
                        resi = m2.group() if m2 else None
                    print(f"[TikTok Page {page_num}] resi={resi} order_id={order_id} items={len(items)}")
                    return {
                        "page": page_num, "resi": resi,
                        "order_id": order_id, "items": items,
                        "skus": [i['sku'] for i in items],
                        "image_base64": image_b64,
                    }
        except Exception as e:
            print(f"[TikTok Page {page_num}] PDF layer error: {e}, fallback OCR")

    # ── Fallback: OCR Tesseract ───────────────────────────────────
    order_id = _extract_order_id_from_ocr(img_cv)

    if not resi:
        gray     = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh   = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
        ocr_text = pytesseract.image_to_string(thresh)
        m = re.search(r'JX\d{10}', ocr_text, re.IGNORECASE)
        if m:
            resi = m.group()

    items = _extract_items_tiktok(img_cv)

    print(f"[TikTok Page {page_num}] resi={resi} order_id={order_id} items={len(items)}")
    return {
        "page": page_num, "resi": resi,
        "order_id": order_id, "items": items,
        "skus": [i['sku'] for i in items],
        "image_base64": image_b64,
    }

# ──────────────────────────────────────────────────────────────────
# Public API: extract dari PDF
# ──────────────────────────────────────────────────────────────────

def _merge_pages_by_order(results: list[dict]) -> list[dict]:
    """
    Merge halaman-halaman yang memiliki order_id sama menjadi satu entri.
    
    Kasus: Order dengan banyak item kadang terpecah ke 2 halaman:
    - Halaman N: ada resi + order_id, tabel header ada tapi items kosong (overflow ke hal berikut)
    - Halaman N+1: tidak ada resi baru, order_id sama, berisi items lanjutan
    
    Strategy:
    1. Group by order_id (non-None)
    2. Untuk halaman tanpa resi tapi order_id sama dengan halaman sebelumnya → merge items
    3. Halaman tanpa order_id sama sekali → cek apakah order_id sama dengan hasil sebelumnya
       (fallback: jika halaman N+1 tidak punya resi DAN order_id-nya sama dengan N → merge)
    """
    merged = []
    order_id_map = {}  # order_id -> index di merged

    for page_result in results:
        order_id = page_result.get("order_id")
        resi = page_result.get("resi")
        items = page_result.get("items", [])

        if order_id and order_id in order_id_map:
            # Sudah ada entri dengan order_id ini → merge items
            idx = order_id_map[order_id]
            existing = merged[idx]
            
            # Gabungkan items (hindari duplikat SKU)
            existing_skus = {i["sku"] for i in existing["items"]}
            for item in items:
                if item["sku"] not in existing_skus:
                    existing["items"].append(item)
                    existing["skus"].append(item["sku"])
                    existing_skus.add(item["sku"])
            
            # Ambil resi jika halaman sebelumnya belum punya
            if not existing.get("resi") and resi:
                existing["resi"] = resi
            
            # Simpan semua page numbers yang terlibat
            existing.setdefault("pages", [existing["page"]]).append(page_result["page"])
            
            print(f"[Merge] Page {page_result['page']} merged into order_id={order_id} "
                  f"(+{len(items)} items, total={len(existing['items'])})")
        else:
            # Entri baru
            entry = dict(page_result)
            entry["pages"] = [page_result["page"]]
            merged.append(entry)
            if order_id:
                order_id_map[order_id] = len(merged) - 1

    return merged


def extract_multiple_resi_tiktok_from_pdf(pdf_path: str) -> list[dict]:
    print(f"[TikTok Multiple] Converting PDF: {pdf_path}")
    images  = convert_from_path(pdf_path, dpi=150)
    results = []

    print(f"[TikTok Multiple] Total {len(images)} halaman")

    for page_num, img_pil in enumerate(images, start=1):
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
        try:
            result = _process_single_page(img_cv, page_num, pdf_path=pdf_path)
        except Exception as e:
            print(f"[TikTok Multiple] Page {page_num} error: {e}")
            traceback.print_exc()
            result = {
                "page"        : page_num,
                "resi"        : None,
                "order_id"    : None,
                "items"       : [],
                "skus"        : [],
                "image_base64": _cv_to_base64_jpeg(img_cv),
            }
        results.append(result)

    # ── POST-PROCESSING: merge halaman dengan order_id sama ──────
    merged_results = _merge_pages_by_order(results)
    
    print(f"[TikTok Multiple] Selesai. {len(images)} halaman → "
          f"{len(merged_results)} resi unik setelah merge.")
    return merged_results
# ──────────────────────────────────────────────────────────────────
# Public API: extract dari image
# ──────────────────────────────────────────────────────────────────

def extract_multiple_resi_tiktok_from_image(image_path: str) -> list[dict]:
    img_cv = cv2.imread(image_path)
    h, w   = img_cv.shape[:2]
    aspect = w / h
    results = []

    if aspect > 1.6:
        print(f"[TikTok Image] Layout 2-kolom (aspect={aspect:.2f})")
        mid_x  = w // 2
        for idx, sl in enumerate([img_cv[:, 0:mid_x], img_cv[:, mid_x:w]], start=1):
            results.append(_process_single_page(sl, idx))

    elif aspect < 0.5:
        estimated_unit_h = 800
        n_units = max(1, round(h / estimated_unit_h))
        print(f"[TikTok Image] {n_units} resi vertikal (aspect={aspect:.2f})")
        unit_h = h // n_units
        for idx in range(n_units):
            y1 = idx * unit_h
            y2 = (idx + 1) * unit_h if idx < n_units - 1 else h
            results.append(_process_single_page(img_cv[y1:y2, :], idx + 1))

    else:
        print(f"[TikTok Image] Single resi (aspect={aspect:.2f})")
        results.append(_process_single_page(img_cv, 1))

    print(f"[TikTok Image] Total {len(results)} resi")
    return results