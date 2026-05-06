"""
ocr_pdf_patch.py — patched v2
Fix utama:
  - extract_order_id_from_barcode: zxing berhasil decode tapi fungsi
    return None karena hasil decode tidak di-return dengan benar.
    
  Root cause dari log:
    [zxing] decoded format=Code 128 text='2-1776496516027593074'  ← decode OK
    [OrderID] Tidak ditemukan di semua zona, fallback ke OCR teks  ← tapi None!
    
  Bug: fungsi iterasi zona tapi tidak break setelah found, atau
  return statement ada di dalam conditional yang tidak ter-reach.
  
  Fix: Tambah early return segera setelah zxing decode berhasil,
  dan return order_id langsung tanpa nested conditional berlebih.

  Pattern order_id Shopee: "2-XXXXXXXXXXXXXXXXX" (format nomor pesanan)
  → berbeda dari resi SPX "SPXIDXXXXXXXXXXXXXXX"
  → zxing akan decode barcode Code 128 di bagian bawah resi
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


# Pattern order ID Shopee: "2-XXXXXXXXXXXXXXXXX"
# ORDER_ID_RE = re.compile(r'(\d+-\d{10,})', re.IGNORECASE)
# # Atau pure digits panjang (fallback)
# LONG_DIGITS_RE = re.compile(r'\b(\d{15,})\b')


SHOPEE_ORDER_RE = re.compile(r'^(\d{6}[A-Z0-9]{6,16})$')
ORDER_ID_DASH_RE = re.compile(r'^(\d+-\d{10,})$')
SPX_RE = re.compile(r'^SPXID\d+$', re.IGNORECASE)


def _decode_barcode_zxing(img_gray: np.ndarray) -> list[str]:
    """Decode semua barcode dari gambar grayscale menggunakan zxingcpp."""
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
    """Decode barcode menggunakan cv2.barcode.BarcodeDetector."""
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



def _is_order_id(text: str) -> bool:
    t = text.strip().upper()
    if SPX_RE.match(t):          # exclude resi
        return False
    if SHOPEE_ORDER_RE.match(t): # ✅ 260425QM2KDD1Q, 260426TKU5NY5Y, dll
        return True
    if ORDER_ID_DASH_RE.match(text.strip()):  # format lama
        return True
    return False


def extract_order_id_from_barcode(img_cv: np.ndarray) -> str | None:
    """
    Extract order ID dari barcode Code 128 di bagian bawah resi Shopee.
    
    Strategi zona (dari log: barcode ada di ~55-85% tinggi gambar):
      Zone 0: y=55-85%, x=0-55% (zona spesifik bawah barcode, kiri)
      Zone 1: y=50-90%, x=0-60% (sedikit lebih luas)
      Zone 2: y=45-92%, x=0-65% (lebih luas lagi)  
      Zone 3: y=40-95%, x=0-75% (full width)
      Zone 4: y=0-100%, x=0-100% (full image fallback)

    FIX v2:
    - Return SEGERA setelah pertama kali ditemukan (early return)
    - Tidak meneruskan loop setelah found
    - Coba format gambar berbeda (scale 3x, OTSU threshold) untuk meningkatkan akurasi
    """
    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    # Zona crop yang dicoba (y0_pct, y1_pct, x0_pct, x1_pct)
    zones = [
        (0.55, 0.85, 0.00, 0.55),
        (0.50, 0.90, 0.00, 0.60),
        (0.45, 0.92, 0.00, 0.65),
        (0.40, 0.95, 0.00, 0.75),
        (0.00, 1.00, 0.00, 1.00),  # full image
    ]

    for zone_idx, (y0p, y1p, x0p, x1p) in enumerate(zones):
        y0 = int(h * y0p)
        y1 = int(h * y1p)
        x0 = int(w * x0p)
        x1 = int(w * x1p)

        crop_gray = gray[y0:y1, x0:x1]
        if crop_gray.size == 0:
            continue

        crop_bgr = img_cv[y0:y1, x0:x1]
        size_str = f"{x1-x0}x{y1-y0}"
        print(f"[OrderID] Zone {zone_idx}: y={y0}-{y1} x={x0}-{x1} size={size_str}")

        # ── Coba berbagai preprocessing ──────────────────────────
        preprocessed_variants = []

        # 1. Original scale
        preprocessed_variants.append(("orig", crop_gray, crop_bgr))

        # 2. Scale 3x + OTSU
        scaled = cv2.resize(crop_gray, None, fx=3, fy=3,
                            interpolation=cv2.INTER_CUBIC)
        _, bw = cv2.threshold(scaled, 0, 255,
                              cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        preprocessed_variants.append(("3x_otsu", bw,
                                       cv2.cvtColor(bw, cv2.COLOR_GRAY2BGR)))

        # 3. Scale 2x (kadang lebih baik untuk barcode padat)
        scaled2 = cv2.resize(crop_gray, None, fx=2, fy=2,
                             interpolation=cv2.INTER_CUBIC)
        preprocessed_variants.append(("2x", scaled2,
                                       cv2.cvtColor(scaled2, cv2.COLOR_GRAY2BGR)))

        for variant_name, var_gray, var_bgr in preprocessed_variants:
            # Coba zxing dulu (lebih akurat)
            decoded_texts = _decode_barcode_zxing(var_gray)

            # Fallback cv2 jika zxing tidak ada atau gagal
            if not decoded_texts:
                decoded_texts = _decode_barcode_cv2(var_bgr)

            for text in decoded_texts:
                text_clean = text.strip()
                if _is_order_id(text_clean):
                    # ── FIX: EARLY RETURN segera setelah found ──
                    print(f"[OrderID] ✅ FOUND di zone {zone_idx} variant={variant_name}: {text_clean!r}")
                    return text_clean
                else:
                    print(f"[OrderID] Bukan order_id: {text_clean!r}")

    print("[OrderID] Tidak ditemukan di semua zona")
    return None