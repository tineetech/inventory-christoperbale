"""
ocr_tiktok_multiple.py  (v5 - fix continuation page detection)
===============================================================
Fix v5:
  Root cause v4:
    _is_continuation_page() memeriksa:
        result.get('resi') is None AND result.get('order_id') is None
    
    Padahal halaman 22 (lanjutan resi JX9489617439) terbaca:
        resi=None, order_id=584175205435671665, items=1
    
    order_id terbaca dari teks PDF → kondisi "order_id=None" TIDAK terpenuhi
    → halaman 22 TIDAK ter-merge ke halaman 21 → SKU hilang

  Fix v5:
    Halaman lanjutan didefinisikan HANYA berdasarkan absennya resi JX:
        _is_continuation_page() return True jika resi is None
    
    Ini lebih robust karena:
    - Resi JX adalah identifier unik per pengiriman
    - Halaman lanjutan memang tidak punya resi baru
    - order_id boleh terbaca atau tidak (tidak jadi penentu)
    
    Juga tambah guard di merge loop: skip jika halaman punya
    order_id berbeda dari prev (berarti halaman baru, bukan lanjutan).
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

    qr = cv2.QRCodeDetector()
    data, _, _ = qr.detectAndDecode(img_cv)
    if data:
        m = JX_RE.search(data)
        if m:
            return m.group()

    bd = None
    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, infos, _, _ = bd.detectAndDecodeWithType(img_cv)
        if ok:
            for info in infos:
                if info:
                    m = JX_RE.search(info)
                    if m:
                        return m.group()
    except AttributeError:
        bd = None

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
                                return m.group()
            except Exception:
                pass

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
                        return m.group()
    except ImportError:
        pass

    print("[Resi JX] Tidak ditemukan dari barcode, fallback ke OCR teks")
    return None


# ──────────────────────────────────────────────────────────────────
# Baca Order ID via OCR
# ──────────────────────────────────────────────────────────────────

def _extract_order_id_from_ocr(img_cv: np.ndarray) -> str | None:
    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    ORDER_RE   = re.compile(r'Order\s*I[dD]\s*[:\s]*(\d{12,})', re.IGNORECASE)
    LONG_NUM_RE = re.compile(r'\b(\d{15,})\b')

    def _ocr_crop(y0_pct, y1_pct, x0_pct=0.0, x1_pct=1.0, scale=3):
        y0 = int(h * y0_pct); y1 = int(h * y1_pct)
        x0 = int(w * x0_pct); x1 = int(w * x1_pct)
        crop = gray[y0:y1, x0:x1]
        if crop.size == 0:
            return ""
        scaled = cv2.resize(crop, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
        _, bw  = cv2.threshold(scaled, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        return pytesseract.image_to_string(bw, config="--psm 6")

    zones = [
        (0.65, 0.90, 0.0, 1.00),
        (0.72, 0.83, 0.0, 0.55),
        (0.70, 0.85, 0.0, 0.55),
        (0.68, 0.87, 0.0, 0.60),
        (0.60, 1.00, 0.0, 1.00),
    ]

    for (y0p, y1p, x0p, x1p) in zones:
        text = _ocr_crop(y0p, y1p, x0p, x1p)
        m = ORDER_RE.search(text)
        if m:
            return m.group(1)
        nums = LONG_NUM_RE.findall(text)
        if nums:
            return nums[0]

    return None


# ──────────────────────────────────────────────────────────────────
# Extract items via OCR (image)
# ──────────────────────────────────────────────────────────────────

def _extract_items_tiktok(img_cv: np.ndarray) -> list[dict]:
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
    gray = cv2.resize(gray, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    height, width = gray.shape

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

    data = pytesseract.image_to_data(
        table_gray,
        output_type=pytesseract.Output.DICT,
        config="--psm 6"
    )

    words_list = []
    for i, word in enumerate(data['text']):
        if not word.strip() or data['conf'][i] < 0:
            continue
        words_list.append({'text': word, 'x': data['left'][i], 'y': data['top'][i]})

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

    header_row = None
    for row in rows:
        line = " ".join(w['text'] for w in row)
        if re.search(r'product\s*name', line, re.IGNORECASE):
            header_row = row
            break

    if not header_row:
        return []

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

    if 'seller_sku' not in col_x:
        return []

    sku_x        = col_x.get('sku',        width * 0.35)
    seller_sku_x = col_x['seller_sku']
    qty_x        = col_x.get('qty',        width * 0.88)

    b_nama_end   = sku_x
    b_sku_end    = seller_sku_x - 20
    b_seller_end = (seller_sku_x + qty_x) / 2

    header_y = header_row[0]['y']
    items    = []
    current_item = None

    for row in rows:
        if row[0]['y'] <= header_y:
            continue

        line = " ".join(w['text'] for w in row)
        if re.search(r'qty\s*total|order\s*i[dD]', line, re.IGNORECASE):
            break

        nama_parts, sku_parts, seller_sku_parts, qty_parts = [], [], [], []
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

        seller_sku_valid = (
            seller_sku_raw
            and re.match(r'^[A-Za-z0-9\-_]{2,}$', seller_sku_raw)
        )

        if seller_sku_valid:
            if current_item:
                items.append(current_item)
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
                "_qty_set": qty_line != "",
            }
        elif current_item:
            if qty_line and not current_item.get("_qty_set"):
                qty_digits = re.sub(r'\D', '', qty_line)
                try:
                    current_item["qty"] = int(qty_digits) if qty_digits else current_item["qty"]
                    current_item["_qty_set"] = True
                except ValueError:
                    pass
            if sku_line and not nama_line and not seller_sku_raw:
                current_item["variasi"] = (current_item["variasi"] + " " + sku_line).strip()
            elif nama_line:
                current_item["nama"] = (current_item["nama"] + " " + nama_line).strip()
                if sku_line:
                    current_item["variasi"] = (current_item["variasi"] + " " + sku_line).strip()

    if current_item:
        items.append(current_item)

    for item in items:
        item.pop("_qty_set", None)

    return items


# ──────────────────────────────────────────────────────────────────
# Parse items dari PDF text layer (per halaman)
# ──────────────────────────────────────────────────────────────────

def _parse_items_tiktok_from_pdf_page(page) -> list[dict]:
    try:
        words = page.extract_words(x_tolerance=3, y_tolerance=3,
                                   keep_blank_chars=False, use_text_flow=False)
    except Exception as e:
        print(f"[TikTok PDF Words] extract_words error: {e}")
        return []

    if not words:
        return []

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

    page_width = page.width
    col_x      = {}
    saw_seller = False
    seller_x_cand = None
    header_row  = None
    header_idx  = None

    for i, row in enumerate(rows):
        line = " ".join(w['text'] for w in row)
        if re.search(r'product\s*name', line, re.IGNORECASE):
            header_row = row
            header_idx = i
            break

    if not header_row:
        return []

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

    items        = []
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


# ──────────────────────────────────────────────────────────────────
# FIX v5: Deteksi continuation page — HANYA berdasarkan absennya resi JX
# ──────────────────────────────────────────────────────────────────

def _is_continuation_page(result: dict) -> bool:
    """
    FIX v5: Halaman lanjutan = tidak punya resi JX (resi is None).

    Perubahan dari v4:
      v4: resi is None AND order_id is None   ← BUG
          Halaman 22 punya order_id terbaca dari teks PDF
          → kondisi order_id=None TIDAK terpenuhi
          → tidak ter-merge → SKU hilang

      v5: resi is None                        ← FIX
          Cukup cek resi JX saja. Resi JX adalah identifier
          unik per pengiriman. Halaman lanjutan memang tidak
          punya resi baru, order_id boleh ada atau tidak.
    """
    return result.get('resi') is None


# ──────────────────────────────────────────────────────────────────
# Proses 1 halaman
# ──────────────────────────────────────────────────────────────────

def _process_single_page(img_cv: np.ndarray, page_num: int,
                          pdf_path: str = None, pdf_page_obj=None) -> dict:
    print(f"\n[TikTok Page {page_num}] Mulai proses...")

    image_b64 = _cv_to_base64_jpeg(img_cv)

    # 1. Resi dari barcode
    resi = _extract_resi_from_barcode(img_cv)

    # ── Coba PDF text layer ──────────────────────────────────────
    pdfplumber_page = pdf_page_obj

    if pdfplumber_page is None and pdf_path:
        try:
            import pdfplumber
            with pdfplumber.open(pdf_path) as pdf:
                pdfplumber_page = pdf.pages[page_num - 1]
                text  = pdfplumber_page.extract_text() or ""
                items = _parse_items_tiktok_from_pdf_page(pdfplumber_page)
                if not resi:
                    m2 = re.search(r'JX\d{10}', text, re.IGNORECASE)
                    resi = m2.group() if m2 else None
                m = re.search(r'Order\s*I[dD]\s*[:\s]+(\d{15,})', text)
                order_id = m.group(1) if m else None
                print(f"[TikTok Page {page_num}] resi={resi} order_id={order_id} items={len(items)}")
                return {
                    "page": page_num, "resi": resi,
                    "order_id": order_id, "items": items,
                    "skus": [i['sku'] for i in items],
                    "image_base64": image_b64,
                }
        except Exception as e:
            print(f"[TikTok Page {page_num}] PDF layer error: {e}, fallback OCR")

    if pdfplumber_page is not None:
        try:
            text  = pdfplumber_page.extract_text() or ""
            items = _parse_items_tiktok_from_pdf_page(pdfplumber_page)
            if not resi:
                m2 = re.search(r'JX\d{10}', text, re.IGNORECASE)
                resi = m2.group() if m2 else None
            m = re.search(r'Order\s*I[dD]\s*[:\s]+(\d{15,})', text)
            order_id = m.group(1) if m else None
            print(f"[TikTok Page {page_num}] resi={resi} order_id={order_id} items={len(items)}")
            return {
                "page": page_num, "resi": resi,
                "order_id": order_id, "items": items,
                "skus": [i['sku'] for i in items],
                "image_base64": image_b64,
            }
        except Exception as e:
            print(f"[TikTok Page {page_num}] PDF page obj error: {e}, fallback OCR")

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
# FIX v5: Public API — extract dari PDF dengan merge continuation page
# ──────────────────────────────────────────────────────────────────

def extract_multiple_resi_tiktok_from_pdf(pdf_path: str) -> list[dict]:
    """
    Extract semua resi dari PDF multi-halaman TikTok/J&T.

    FIX v5: _is_continuation_page() sekarang hanya cek resi is None,
    bukan resi is None AND order_id is None.

    Contoh kasus yang kini ter-fix:
      Hal. 21: resi=JX9489617439, order_id=584175205435671665, items=[]
      Hal. 22: resi=None, order_id=584175205435671665, items=[{sku=dalbz37}]
               ← v4 tidak merge karena order_id != None
               ← v5 merge karena resi=None ✅

    Guard tambahan: jika halaman lanjutan punya order_id yang berbeda
    dari prev, berarti ini halaman baru → tidak di-merge (append sebagai
    entri baru dengan resi=None, akan dihandle sebagai orphan).
    """
    print(f"[TikTok Multiple] Converting PDF: {pdf_path}")
    images  = convert_from_path(pdf_path, dpi=150)
    raw_results = []

    print(f"[TikTok Multiple] Total {len(images)} halaman")

    try:
        import pdfplumber
        pdf_obj = pdfplumber.open(pdf_path)
        pdf_pages = pdf_obj.pages
    except Exception:
        pdf_obj   = None
        pdf_pages = [None] * len(images)

    for page_num, img_pil in enumerate(images, start=1):
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
        pdf_page = pdf_pages[page_num - 1] if pdf_pages else None
        try:
            result = _process_single_page(img_cv, page_num,
                                           pdf_path=pdf_path,
                                           pdf_page_obj=pdf_page)
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
        raw_results.append(result)

    if pdf_obj:
        pdf_obj.close()

    # ── FIX v5: Merge continuation pages ─────────────────────────
    merged_results = []
    for result in raw_results:
        if _is_continuation_page(result) and merged_results:
            prev = merged_results[-1]

            # Guard: jika order_id berbeda (keduanya ada), ini bukan lanjutan
            # dari prev → append sebagai entri tersendiri
            if (result.get('order_id') is not None
                    and prev.get('order_id') is not None
                    and result['order_id'] != prev['order_id']):
                print(f"[TikTok Multiple] Page {result['page']} resi=None tapi "
                      f"order_id berbeda ({result['order_id']} vs {prev['order_id']}), "
                      f"append sebagai entri baru")
                merged_results.append(result)
                continue

            # Merge items ke resi sebelumnya
            if result['items']:
                prev['items'].extend(result['items'])
                prev['skus'] = [i['sku'] for i in prev['items']]
                print(f"[TikTok Multiple] Page {result['page']} merged "
                      f"({len(result['items'])} items) ke resi {prev['resi']} "
                      f"[order_id={prev['order_id']}]")
            else:
                print(f"[TikTok Multiple] Page {result['page']} adalah "
                      f"continuation page tapi items kosong, skip merge")

            # Simpan image tambahan (opsional)
            if 'extra_images' not in prev:
                prev['extra_images'] = []
            prev['extra_images'].append(result['image_base64'])

        else:
            merged_results.append(result)

    print(f"[TikTok Multiple] Selesai. "
          f"{len(raw_results)} halaman → {len(merged_results)} resi setelah merge.")

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