"""
ocr_pdf_patch.py — patched v3
==============================
Fix utama vs v2:
  - _is_order_id(): SHOPEE_ORDER_RE = r'^(\d{6}[A-Z0-9]{6,16})$'
    sebelumnya hanya cek format tanpa validasi isi 6 digit pertama.
    Resi AnterAja "11003785760273" (14 digit) lolos karena:
      - "110037" (6 digit) + "85760273" (8 alphanum) → match regex
      - Tapi "bulan 00" tidak valid → bukan tanggal Shopee yang valid

  Root cause dari bug order_id = resi AnterAja:
    extract_order_id_from_barcode() decode barcode resi AnterAja
    (isi: "11003785760273") → _is_order_id() return True (salah)
    → result["order_id"] di-set ke "11003785760273"
    → override order_id yang sudah benar dari teks ("260517JYGCEGNW")

  Fix: tambah _is_valid_shopee_date_prefix() yang memvalidasi bahwa
    6 digit pertama adalah tanggal valid format YYMMDD:
    - YY: 20-35 (tahun 2020-2035, range order Shopee yang masuk akal)
    - MM: 01-12
    - DD: 01-31
    "110037..." → bulan=00 → INVALID → _is_order_id() return False ✅
    "260517..." → bulan=05, hari=17 → VALID → _is_order_id() return True ✅
"""

import re
import cv2
import numpy as np

# Coba import zxingcpp
try:
    import zxingcpp
    _ZXING_AVAILABLE = True
    print("[ocr_pdf_patch] zxingcpp tersedia")
except ImportError:
    _ZXING_AVAILABLE = False
    print("[ocr_pdf_patch] zxingcpp TIDAK tersedia, akan pakai cv2 BarcodeDetector")


# ──────────────────────────────────────────────────────────────────────────────
# Pattern order ID Shopee
# ──────────────────────────────────────────────────────────────────────────────

# Format standar: 6 digit (YYMMDD) + 6-16 alphanum, contoh: 260517JYGCEGNW
SHOPEE_ORDER_RE  = re.compile(r'^(\d{6}[A-Z0-9]{6,16})$')
# Format lama dengan dash: 2-XXXXXXXXXXXXXXXXX
ORDER_ID_DASH_RE = re.compile(r'^(\d+-\d{10,})$')
# Pattern resi SPX — exclude
SPX_RE = re.compile(r'^SPXID\d+$', re.IGNORECASE)


def _is_valid_shopee_date_prefix(s: str) -> bool:
    """
    Validasi bahwa 6 karakter pertama adalah tanggal valid YYMMDD.

    Shopee order ID selalu diawali tanggal pembuatan pesanan:
      YY = 2 digit tahun (20-35, range 2020-2035)
      MM = 2 digit bulan (01-12)
      DD = 2 digit hari  (01-31)

    Ini mencegah false positive dari resi ekspedisi yang kebetulan
    panjang 14+ karakter dan 6 digit pertamanya bukan tanggal valid.
    Contoh: resi AnterAja "11003785760273"
      → "110037" → bulan=00 → INVALID → bukan order_id ✅
    """
    if len(s) < 6 or not s[:6].isdigit():
        return False
    yy = int(s[0:2])
    mm = int(s[2:4])
    dd = int(s[4:6])
    return (20 <= yy <= 35) and (1 <= mm <= 12) and (1 <= dd <= 31)


def _is_order_id(text: str) -> bool:
    """
    Cek apakah teks adalah order ID Shopee yang valid.

    Urutan pengecekan:
    1. Exclude resi SPX (SPXID...)
    2. Format standar: 6 digit YYMMDD + alphanum, DAN tanggal harus valid
    3. Format lama: digit-digit panjang (dengan dash)
    """
    t = text.strip().upper()

    # 1. Exclude resi SPX
    if SPX_RE.match(t):
        return False

    # 2. Format standar Shopee order ID
    if SHOPEE_ORDER_RE.match(t):
        # ── FIX v3: validasi 6 digit pertama adalah tanggal valid ──
        if not _is_valid_shopee_date_prefix(t):
            print(f"[OrderID] Rejected '{t}': 6 digit pertama bukan tanggal valid YYMMDD")
            return False
        return True

    # 3. Format lama dengan dash
    if ORDER_ID_DASH_RE.match(text.strip()):
        return True

    return False


# ──────────────────────────────────────────────────────────────────────────────
# Barcode decoder helpers
# ──────────────────────────────────────────────────────────────────────────────

def _decode_barcode_zxing(img_gray: np.ndarray) -> list[str]:
    if not _ZXING_AVAILABLE:
        return []
    results = []
    try:
        barcodes = zxingcpp.read_barcodes(img_gray)
        for b in barcodes:
            if b.text:
                results.append(b.text)
                print(f"[zxing] decoded format={b.format} text={b.text!r}")
    except Exception as e:
        print(f"[zxing] error: {e}")
    return results


def _decode_barcode_cv2(img_bgr: np.ndarray) -> list[str]:
    results = []
    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, decoded_info, decoded_type, _ = bd.detectAndDecodeWithType(img_bgr)
        if ok:
            for info in decoded_info:
                if info:
                    results.append(info)
                    print(f"[cv2 barcode] decoded: {info!r}")
    except AttributeError:
        pass
    except Exception as e:
        print(f"[cv2 barcode] error: {e}")
    return results


# ──────────────────────────────────────────────────────────────────────────────
# Fungsi utama
# ──────────────────────────────────────────────────────────────────────────────

def extract_order_id_from_barcode(img_cv: np.ndarray) -> str | None:
    """
    Extract order ID dari barcode Code 128 di bagian bawah resi Shopee.

    Strategi zona (barcode order_id ada di ~40-95% tinggi gambar):
      Zone 0: y=55-85%, x=0-55%
      Zone 1: y=50-90%, x=0-60%
      Zone 2: y=45-92%, x=0-65%
      Zone 3: y=40-95%, x=0-75%
      Zone 4: y=0-100%,  x=0-100% (full image fallback)

    Validasi hasil decode via _is_order_id() yang sudah difix:
      - Resi AnterAja 14 digit tidak akan lolos karena bulan=00 tidak valid
      - Order ID Shopee 260517JYGCEGNW lolos karena bulan=05 valid
    """
    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    zones = [
        (0.55, 0.85, 0.00, 0.55),
        (0.50, 0.90, 0.00, 0.60),
        (0.45, 0.92, 0.00, 0.65),
        (0.40, 0.95, 0.00, 0.75),
        (0.00, 1.00, 0.00, 1.00),
    ]

    for zone_idx, (y0p, y1p, x0p, x1p) in enumerate(zones):
        y0 = int(h * y0p); y1 = int(h * y1p)
        x0 = int(w * x0p); x1 = int(w * x1p)

        crop_gray = gray[y0:y1, x0:x1]
        if crop_gray.size == 0:
            continue

        crop_bgr = img_cv[y0:y1, x0:x1]
        print(f"[OrderID] Zone {zone_idx}: y={y0}-{y1} x={x0}-{x1}")

        preprocessed_variants = [("orig", crop_gray, crop_bgr)]

        # Scale 3x + OTSU
        scaled = cv2.resize(crop_gray, None, fx=3, fy=3,
                            interpolation=cv2.INTER_CUBIC)
        _, bw = cv2.threshold(scaled, 0, 255,
                              cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        preprocessed_variants.append(("3x_otsu", bw,
                                       cv2.cvtColor(bw, cv2.COLOR_GRAY2BGR)))

        # Scale 2x
        scaled2 = cv2.resize(crop_gray, None, fx=2, fy=2,
                             interpolation=cv2.INTER_CUBIC)
        preprocessed_variants.append(("2x", scaled2,
                                       cv2.cvtColor(scaled2, cv2.COLOR_GRAY2BGR)))

        for variant_name, var_gray, var_bgr in preprocessed_variants:
            decoded_texts = _decode_barcode_zxing(var_gray)
            if not decoded_texts:
                decoded_texts = _decode_barcode_cv2(var_bgr)

            for text in decoded_texts:
                text_clean = text.strip()
                if _is_order_id(text_clean):
                    print(f"[OrderID] ✅ FOUND zone={zone_idx} variant={variant_name}: {text_clean!r}")
                    return text_clean
                else:
                    print(f"[OrderID] Bukan order_id: {text_clean!r}")

    print("[OrderID] Tidak ditemukan di semua zona")
    return None