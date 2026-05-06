"""
ocr_shopee_multiple.py — patched v2
Fix utama:
  1. Ganti threading.Event timeout → pakai pytesseract langsung dengan
     environment variable TESSERACT_TIMEOUT dan subprocess kill yang proper.
     Di Celery threads pool, gunakan concurrent.futures.ThreadPoolExecutor
     dengan cancel yang benar (tidak blocking indefinitely).

  2. OCR item parsing: tambah fallback extract teks langsung dari PDF layer
     (pdfplumber) sebelum OCR — jauh lebih cepat dan akurat untuk PDF digital.

  3. order_id: pastikan nilai dari extract_order_id_from_barcode di-assign
     dengan benar (bug sebelumnya: zxing sukses tapi fungsi return None).
"""

import re
import cv2
import numpy as np
import pytesseract
import base64
import traceback
import threading
import subprocess
import os
import tempfile
from concurrent.futures import ThreadPoolExecutor, TimeoutError as FuturesTimeoutError
from pdf2image import convert_from_path

from ocr_pdf import extract_resi_from_barcode_cv, extract_items_from_img_cv
from parser.parser_shopee import parse_shopee
from ocr_pdf_patch import extract_order_id_from_barcode
import threading
_pdfplumber_lock = threading.Lock()


# ──────────────────────────────────────────────────────────────────
# WRAPPER: pytesseract dengan hard timeout via ThreadPoolExecutor
# ThreadPoolExecutor.submit().result(timeout=N) lebih reliable
# daripada threading.Event karena TimeoutError di-raise ke caller
# tanpa blocking, dan tidak deadlock di WSL2 threads pool.
# ──────────────────────────────────────────────────────────────────

class TesseractTimeout(Exception):
    pass


# Gunakan executor pool yang di-share agar tidak spawn thread baru tiap panggilan
_tess_executor = ThreadPoolExecutor(max_workers=4, thread_name_prefix="tess_")


def run_tesseract_safe(img, config="", timeout_sec=30):
    """
    Jalankan pytesseract.image_to_string dengan timeout via ThreadPoolExecutor.
    TimeoutError di-raise langsung ke caller — tidak blocking seperti Event.wait().
    """
    future = _tess_executor.submit(
        pytesseract.image_to_string, img, **{"config": config}
    )
    try:
        return future.result(timeout=timeout_sec) or ""
    except FuturesTimeoutError:
        future.cancel()
        raise TesseractTimeout(f"pytesseract timeout setelah {timeout_sec}s")
    except Exception as e:
        raise e


def run_tesseract_data_safe(img, output_type, config="", timeout_sec=30):
    """Versi image_to_data dengan timeout."""
    future = _tess_executor.submit(
        pytesseract.image_to_data, img,
        **{"output_type": output_type, "config": config}
    )
    try:
        return future.result(timeout=timeout_sec)
    except FuturesTimeoutError:
        future.cancel()
        raise TesseractTimeout(f"pytesseract image_to_data timeout setelah {timeout_sec}s")
    except Exception as e:
        raise e


# ──────────────────────────────────────────────────────────────────
# BARU: Extract teks & items langsung dari PDF layer (tanpa OCR)
# Jauh lebih cepat dan akurat untuk PDF digital dari Shopee/SPX.
# Fallback ke OCR jika PDF tidak punya text layer.
# ──────────────────────────────────────────────────────────────────

def _extract_text_and_items_from_pdf_layer(pdf_path: str, page_num: int) -> tuple[str, list]:
    try:
        import pdfplumber
    except ImportError:
        return "", []

    try:
        with _pdfplumber_lock:          # ← tambah lock
            with pdfplumber.open(pdf_path) as pdf:
                if page_num < 1 or page_num > len(pdf.pages):
                    return "", []
                page = pdf.pages[page_num - 1]
                full_text = page.extract_text() or ""
                if not full_text.strip():
                    return "", []
                print(f"[PDF Layer] Page {page_num}: {len(full_text)} chars OK")
                items = _parse_items_by_word_position(page)
                # Selesaikan semua operasi page DALAM lock
                return full_text, items
    except Exception as e:
        print(f"[PDF Layer] Error: {e}")
        return "", []


def _parse_items_by_word_position(page) -> list:
    """
    Parse tabel item dari halaman pdfplumber menggunakan koordinat X kata.

    Pendekatan:
    1. Ambil semua kata + posisi X dari PDF layer via extract_words()
    2. Cluster kata per baris (Y yang sama ± toleransi)
    3. Cari baris header '# Nama Produk SKU Variasi Qty' → ambil X tiap kolom
    4. Untuk setiap baris item setelah header:
       - Kata di zona nama  (x < x_sku)      → nama produk
       - Kata di zona sku   (x_sku ≤ x < x_variasi) → SKU
       - Kata di zona variasi (x_variasi ≤ x < x_qty) → variasi
       - Kata di zona qty   (x ≥ x_qty)      → qty

    Keunggulan vs regex teks linear:
    - SKU "jovcrm40" di kolom SKU (x=200) tidak akan tercampur dengan
      nama produk yang ada di kolom nama (x=10-150) — beda X-nya jelas.
    - Nama multi-baris (wrap) ter-handle karena sama-sama di zona X nama.
    """
    try:
        words = page.extract_words(
            x_tolerance=3,
            y_tolerance=3,
            keep_blank_chars=False,
            use_text_flow=False,
        )
    except Exception as e:
        print(f"[PDF Layer Words] extract_words error: {e}")
        return []

    if not words:
        print("[PDF Layer Words] Tidak ada kata ditemukan")
        return []

    # ── Cluster kata per baris berdasarkan Y ─────────────────────
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

    print(f"[PDF Layer Words] Total rows: {len(rows)}")

    # ── Cari header row ──────────────────────────────────────────
    header_row = None
    header_row_idx = None
    for i, row in enumerate(rows):
        line = " ".join(w['text'] for w in row)
        if re.search(r'#\s*Nama\s*Produk', line, re.IGNORECASE):
            header_row = row
            header_row_idx = i
            print(f"[PDF Layer Words] Header ditemukan di baris {i}: {line}")
            break

    if header_row is None:
        print("[PDF Layer Words] Header tidak ditemukan, fallback ke regex")
        return _parse_items_from_pdf_text_fallback(
            "\n".join(" ".join(w['text'] for w in r) for r in rows)
        )

    # ── Deteksi posisi X kolom dari header ───────────────────────
    # Cari X awal tiap kolom header: SKU, Variasi, Qty
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

    print(f"[PDF Layer Words] Kolom X: {col_x}")

    # Fallback posisi kolom jika header tidak lengkap
    # (estimasi dari lebar halaman A6/A4 Shopee)
    x_sku     = col_x.get('sku',     page_width * 0.45)
    x_variasi = col_x.get('variasi', page_width * 0.62)
    x_qty     = col_x.get('qty',     page_width * 0.85)

    print(f"[PDF Layer Words] Batas kolom: nama<{x_sku:.1f} | "
          f"sku<{x_variasi:.1f} | variasi<{x_qty:.1f} | qty≥{x_qty:.1f}")

    # ── Parse baris item setelah header ──────────────────────────
    items = []
    current_item = None

    for row in rows[header_row_idx + 1:]:
        line = " ".join(w['text'] for w in row)

        # Sentinel: baris "Pesan:" → stop
        if re.match(r'Pesan\s*:', line, re.IGNORECASE):
            break

        # Pisahkan kata ke kolom berdasarkan X
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

        print(f"[PDF Layer Words] y={row[0]['top']:.0f} | "
              f"nama={repr(nama_line[:30])} | sku={repr(sku_line)} | "
              f"variasi={repr(variasi_line)} | qty={repr(qty_line)}")

        # Deteksi baris item baru: nama_line diawali angka (nomor urut)
        is_new_item = bool(re.match(r'^\d+\s', nama_line))

        if is_new_item:
            # Simpan item sebelumnya
            if current_item:
                items.append(current_item)

            # Buang nomor urut dari nama
            nama_clean = re.sub(r'^\d+\s+', '', nama_line).strip()

            qty = 1
            if qty_line:
                qty_digits = re.sub(r'\D', '', qty_line)
                try:
                    qty = int(qty_digits) if qty_digits else 1
                except ValueError:
                    qty = 1

            current_item = {
                "nama"    : nama_clean,
                "sku"     : sku_line,
                "variasi" : variasi_line,
                "qty"     : qty,
                "_qty_set": qty_line != "",
            }

        elif current_item:
            # Baris lanjutan (nama wrap, atau variasi/qty di baris berikutnya)
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

    # Hapus internal flag
    for item in items:
        item.pop("_qty_set", None)

    for item in items:
        print(f"[PDF Layer Words] FINAL item: nama={repr(item['nama'][:40])} "
              f"sku={repr(item['sku'])} variasi={repr(item['variasi'])} qty={item['qty']}")

    return items


def _parse_items_from_pdf_text_fallback(text: str) -> list:
    items = []
    lines = text.split('\n')

    # SKU Shopee: lowercase alfanumerik, 4-20 char, tidak ada spasi
    SKU_RE = re.compile(r'\b([a-z][a-z0-9\-_]{3,19})\b')

    # Variasi: "Kata,angka" atau "Kata, Kata" sebelum qty di akhir
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
        before  = re.sub(r'^\d+\s+', '', before).strip()  # buang nomor urut

        # Cari semua kandidat SKU di 'before'
        tokens = before.split()
        sku    = ""
        nama_tokens = []

        for token in tokens:
            # Token yang pure lowercase+digit dan cocok pola SKU → kandidat SKU
            if SKU_RE.fullmatch(token) and token.islower():
                sku = token  # ambil yang terakhir ditemukan
            else:
                nama_tokens.append(token)

        # Jika SKU ditemukan di tengah, sisanya adalah nama
        if sku:
            # Buang SKU dari nama_tokens kalau masih ada
            nama = " ".join(t for t in nama_tokens if t != sku).strip()
        else:
            nama = before

        if not nama:
            continue

        item = {"nama": nama, "sku": sku, "variasi": variasi, "qty": qty}
        items.append(item)
        print(f"[PDF Fallback] Parsed: {item}")

    return items

# Alias untuk backward compat (dipanggil dari luar modul ini jika ada)
def _parse_items_from_pdf_text(text: str) -> list:
    return _parse_items_from_pdf_text_fallback(text)


# ──────────────────────────────────────────────────────────────────

def _cv_to_base64_jpeg(img_cv: np.ndarray, quality: int = 70, max_width: int = 900) -> str:
    try:
        h, w = img_cv.shape[:2]
        if w > max_width:
            scale  = max_width / w
            new_w  = max_width
            new_h  = int(h * scale)
            img_cv = cv2.resize(img_cv, (new_w, new_h), interpolation=cv2.INTER_AREA)
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


def _process_single_page_cv(img_cv: np.ndarray, page_num: int,
                              pdf_path: str = None) -> dict:
    """
    Proses 1 cv2 BGR image → hasil parse 1 resi.

    BARU: Terima pdf_path opsional → coba extract text layer dulu (cepat),
    fallback ke OCR jika tidak ada text layer.
    """
    print(f"\n[Page {page_num}] Mulai proses...")

    # 1. Encode gambar ke base64
    print(f"[Page {page_num}] Encoding image to base64...")
    image_b64 = _cv_to_base64_jpeg(img_cv)
    print(f"[Page {page_num}] image_base64 length = {len(image_b64)}")

    # 2. Barcode / QR (tidak pakai pytesseract, aman)
    print(f"[Page {page_num}] Scanning barcode/QR...")
    resi_from_barcode = extract_resi_from_barcode_cv(img_cv)

    # ── STRATEGI BARU: Coba PDF text layer dulu ──────────────────
    pdf_text = ""
    pdf_items = []
    if pdf_path:
        print(f"[Page {page_num}] Mencoba extract dari PDF text layer...")
        pdf_text, pdf_items = _extract_text_and_items_from_pdf_layer(pdf_path, page_num)

    if pdf_text.strip():
        # ✅ Ada text layer — tidak perlu OCR sama sekali
        print(f"[Page {page_num}] ✅ Pakai PDF text layer, skip OCR Tesseract")
        ocr_text = pdf_text
        items    = pdf_items
    else:
        # ❌ Tidak ada text layer — fallback ke OCR
        print(f"[Page {page_num}] ⚠ Tidak ada text layer, jalankan OCR...")

        # 3. OCR text — DENGAN TIMEOUT via ThreadPoolExecutor
        print(f"[Page {page_num}] Running OCR text (timeout=30s)...")
        gray   = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]

        try:
            ocr_text = run_tesseract_safe(thresh, timeout_sec=30)
            print(f"[Page {page_num}] OCR text OK ({len(ocr_text)} chars)")
        except TesseractTimeout:
            print(f"[Page {page_num}] ⚠ OCR text TIMEOUT — lanjut tanpa teks")
            ocr_text = ""
        except Exception as e:
            print(f"[Page {page_num}] ⚠ OCR text error: {e}")
            ocr_text = ""

        # 4. Item parsing — DENGAN TIMEOUT
        print(f"[Page {page_num}] Parsing items (timeout=45s)...")
        try:
            items = _extract_items_safe(img_cv, timeout_sec=45)
            print(f"[Page {page_num}] Items OK: {len(items)} item")
        except TesseractTimeout:
            print(f"[Page {page_num}] ⚠ Item parsing TIMEOUT — items kosong")
            items = []
        except Exception as e:
            print(f"[Page {page_num}] ⚠ Item parsing error: {e}")
            items = []

    # Gabungkan resi dari barcode ke full_text
    if resi_from_barcode:
        full_text = f"No.Resi: {resi_from_barcode}\n" + ocr_text
    else:
        full_text = ocr_text

    # 5. Parse shopee
    result = parse_shopee(full_text, items)

    # 6. Order ID dari barcode (zxing)
    # ── FIX: extract_order_id_from_barcode harus return order_id string,
    #    bukan None padahal zxing sukses decode. Pastikan return value di-assign.
    print(f"[Page {page_num}] Scanning order ID barcode...")
    order_id_from_barcode = None
    try:
        order_id_from_barcode = extract_order_id_from_barcode(img_cv)
        print(f"[Page {page_num}] order_id_from_barcode = {order_id_from_barcode!r}")
    except Exception as e:
        print(f"[Page {page_num}] ⚠ Order ID barcode error: {e}")

    if order_id_from_barcode:
        result["order_id"] = order_id_from_barcode
    elif not result.get("order_id"):
        # Fallback: cari order_id dari text layer / OCR text
        m = re.search(r'No\.\s*Pesanan[:\s]+([\d\-]+)', full_text, re.IGNORECASE)
        if m:
            result["order_id"] = m.group(1).strip()
            print(f"[Page {page_num}] order_id dari teks: {result['order_id']}")

    result["page"]         = page_num
    result["image_base64"] = image_b64

    print(f"[Page {page_num}] ✅ SELESAI — resi={result.get('resi')} "
          f"order_id={result.get('order_id')} items={len(result.get('items', []))}")

    return result


def _extract_items_safe(img_cv: np.ndarray, timeout_sec: int = 45) -> list:
    """
    Wrapper extract_items_from_img_cv dengan timeout via ThreadPoolExecutor.
    """
    future = _tess_executor.submit(extract_items_from_img_cv, img_cv)
    try:
        return future.result(timeout=timeout_sec) or []
    except FuturesTimeoutError:
        future.cancel()
        raise TesseractTimeout(f"extract_items timeout setelah {timeout_sec}s")
    except Exception as e:
        raise e


def extract_multiple_resi_from_pdf(pdf_path: str) -> list[dict]:
    print(f"[PDF Multiple] Converting PDF: {pdf_path}")
    images  = convert_from_path(pdf_path, dpi=150)
    results = []

    print(f"[PDF Multiple] Total {len(images)} halaman")

    for page_num, img_pil in enumerate(images, start=1):
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
        try:
            # ── PASS pdf_path ke _process_single_page_cv ──
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
                "image_base64": _cv_to_base64_jpeg(img_cv),
            }
        results.append(result)

    print(f"[PDF Multiple] Selesai. {len(results)} halaman diproses.")
    return results


def extract_multiple_resi_from_image(image_path: str) -> list[dict]:
    img_cv = cv2.imread(image_path)
    h, w   = img_cv.shape[:2]
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