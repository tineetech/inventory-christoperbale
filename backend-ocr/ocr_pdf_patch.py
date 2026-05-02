"""
PATCH: Baca No. Pesanan dari barcode 1D menggunakan zxing-cpp
=============================================================

Kenapa zxing-cpp:
  - Tidak butuh DLL eksternal (libzbar-64.dll, libiconv.dll, dll)
  - Support Python 3.13 di Windows
  - Pure wheel — pip install langsung jalan
  - Akurasi bagus untuk barcode 1D tipis

Instalasi:
  pip install zxing-cpp opencv-python numpy

Tidak perlu download DLL apapun.
"""

import re
import cv2
import numpy as np
import traceback

# ──────────────────────────────────────────────────────────────────
# Import zxing-cpp
# ──────────────────────────────────────────────────────────────────

try:
    import zxingcpp
    ZXING_AVAILABLE = True
    print("[zbar] zxing-cpp berhasil diimport ✅")
except ImportError:
    ZXING_AVAILABLE = False
    print("[zbar] ⚠️  zxing-cpp tidak tersedia. Jalankan: pip install zxing-cpp")

# ──────────────────────────────────────────────────────────────────
# Regex format No. Pesanan Shopee: YYMMDD + 7-10 alfanum
# Contoh: 260425R19TG85E, 260426SN0M6DNM
# ──────────────────────────────────────────────────────────────────

ORDER_ID_RE = re.compile(r'\b(\d{6}[A-Z0-9]{7,10})\b', re.IGNORECASE)


# ──────────────────────────────────────────────────────────────────
# Core scan dengan zxing-cpp
# ──────────────────────────────────────────────────────────────────

def _zxing_scan(gray_arr: np.ndarray) -> list[str]:
    """
    Scan grayscale numpy array dengan zxing-cpp.
    Return list decoded strings.
    """
    if not ZXING_AVAILABLE:
        return []
    try:
        results = zxingcpp.read_barcodes(gray_arr)
        decoded = []
        for r in results:
            text = r.text
            fmt  = str(r.format)
            print(f"[zxing] decoded format={fmt} text={repr(text[:50])}")
            decoded.append(text)
        return decoded
    except Exception as e:
        print(f"[zxing] scan error: {e}")
        return []


# ──────────────────────────────────────────────────────────────────
# Preprocessing untuk barcode tipis (~25px tinggi di DPI 150)
# ──────────────────────────────────────────────────────────────────

def _preprocess(crop: np.ndarray, scale: int = 5) -> np.ndarray:
    """
    1. White padding vertikal 40px atas & bawah
    2. Scale up dengan INTER_CUBIC
    3. Otsu threshold
    """
    pad    = np.full((40, crop.shape[1]), 255, dtype=np.uint8)
    padded = np.vstack([pad, crop, pad])
    scaled = cv2.resize(padded, None, fx=scale, fy=scale,
                        interpolation=cv2.INTER_CUBIC)
    _, bw  = cv2.threshold(scaled, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return bw


def _preprocess_inverted(crop: np.ndarray, scale: int = 5) -> np.ndarray:
    """Sama seperti _preprocess tapi warna diinvert (fallback)."""
    bw = _preprocess(crop, scale)
    return cv2.bitwise_not(bw)


# ──────────────────────────────────────────────────────────────────
# Fungsi utama
# ──────────────────────────────────────────────────────────────────

def extract_order_id_from_barcode(img_cv: np.ndarray):
    """
    Baca No. Pesanan dari barcode 1D di area kiri bawah resi Shopee.

    Layout resi Shopee (portrait, DPI 150 ≈ 455x639px):
      ┌─────────────────────────────────┐
      │  Header (logo, resi, alamat)    │  0-48%
      ├──────────────────┬──────────────┤
      │ [BARCODE tipis]  │  [QR resi]   │  48-68%  <- TARGET (kiri)
      ├──────────────────┴──────────────┤
      │  Tabel item produk              │  68-100%
      └─────────────────────────────────┘

    Returns:
        str  -> No. Pesanan (mis. "260425R19TG85E")
        None -> tidak ditemukan, fallback ke OCR teks
    """
    if not ZXING_AVAILABLE:
        print("[OrderID] zxing-cpp tidak tersedia, fallback ke OCR teks")
        return None

    try:
        h, w = img_cv.shape[:2]
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

        # Zona sweep: (y_start%, y_end%, x_end%)
        # Dari ketat -> lebar supaya tidak menangkap QR di kanan
        zones = [
            (0.48, 0.62, 0.58),
            (0.45, 0.65, 0.60),
            (0.42, 0.68, 0.62),
            (0.38, 0.72, 0.65),
        ]

        for idx, (y0p, y1p, x1p) in enumerate(zones):
            y0   = int(h * y0p)
            y1   = int(h * y1p)
            x1   = int(w * x1p)
            crop = gray[y0:y1, 0:x1]

            if crop.size == 0 or crop.shape[0] < 5:
                continue

            print(f"[OrderID] Zone {idx}: y={y0}-{y1} x=0-{x1} "
                  f"size={crop.shape[1]}x{crop.shape[0]}")

            # Coba 4 strategi per zona, berhenti begitu ketemu
            strategies = [
                ("5x Otsu",          _preprocess(crop, scale=5)),
                ("3x Otsu",          _preprocess(crop, scale=3)),
                ("raw crop",         crop),
                ("5x Otsu inverted", _preprocess_inverted(crop, scale=5)),
            ]

            for strat_name, img_to_scan in strategies:
                for decoded in _zxing_scan(img_to_scan):
                    m = ORDER_ID_RE.search(decoded)
                    if m:
                        order_id = m.group(1).upper()
                        print(f"[OrderID] Zone {idx} [{strat_name}] -> {order_id}")
                        return order_id

        print("[OrderID] Tidak ditemukan di semua zona, fallback ke OCR teks")
        return None

    except Exception as e:
        print(f"[OrderID] Exception: {e}")
        traceback.print_exc()
        return None


# ──────────────────────────────────────────────────────────────────
# Debug: simpan crop ke PNG untuk troubleshooting visual
# ──────────────────────────────────────────────────────────────────

def debug_save_barcode_crop(img_cv: np.ndarray, output_dir: str = "debug_crops"):
    """
    Simpan semua crop zona ke PNG.
    Panggil kalau order_id masih tidak terbaca.

    Usage:
        from ocr_pdf_patch import debug_save_barcode_crop
        debug_save_barcode_crop(img_cv, "debug_crops")
    """
    import os
    os.makedirs(output_dir, exist_ok=True)

    h, w = img_cv.shape[:2]
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    zones = [
        (0.48, 0.62, 0.58),
        (0.45, 0.65, 0.60),
        (0.42, 0.68, 0.62),
        (0.38, 0.72, 0.65),
    ]

    for idx, (y0p, y1p, x1p) in enumerate(zones):
        y0, y1, x1 = int(h*y0p), int(h*y1p), int(w*x1p)
        crop = gray[y0:y1, 0:x1]
        if crop.size == 0:
            continue

        cv2.imwrite(f"{output_dir}/zone{idx}_raw.png", crop)
        cv2.imwrite(f"{output_dir}/zone{idx}_5x.png",  _preprocess(crop, 5))
        cv2.imwrite(f"{output_dir}/zone{idx}_3x.png",  _preprocess(crop, 3))
        print(f"[debug] zone{idx} -> {output_dir}/zone{idx}_*.png")

    print(f"\nBuka folder '{output_dir}' dan pastikan barcode terlihat jelas.")
    print("Kalau barcode terpotong -> sesuaikan y% di extract_order_id_from_barcode()")


# ──────────────────────────────────────────────────────────────────
# CLI test: python ocr_pdf_patch.py resi.pdf [--debug]
# ──────────────────────────────────────────────────────────────────

if __name__ == "__main__":
    import sys

    if not ZXING_AVAILABLE:
        print("\n zxing-cpp tidak terinstall!")
        print("Jalankan: pip install zxing-cpp")
        sys.exit(1)

    if len(sys.argv) < 2:
        print("Usage:")
        print("  python ocr_pdf_patch.py <resi.pdf|resi.png> [--debug]")
        sys.exit(0)

    path     = sys.argv[1]
    do_debug = "--debug" in sys.argv
    ext      = path.rsplit(".", 1)[-1].lower()

    if ext == "pdf":
        from pdf2image import convert_from_path
        images = convert_from_path(path, dpi=150)
        print(f"Total halaman: {len(images)}\n")
        for i, pil_img in enumerate(images, start=1):
            img_cv = cv2.cvtColor(np.array(pil_img), cv2.COLOR_RGB2BGR)
            if do_debug:
                debug_save_barcode_crop(img_cv, f"debug_crops/page{i}")
            result = extract_order_id_from_barcode(img_cv)
            print(f"  Page {i}: order_id = {result}\n")

    elif ext in ("png", "jpg", "jpeg"):
        img_cv = cv2.imread(path)
        if img_cv is None:
            print(f"Gagal baca: {path}")
            sys.exit(1)
        if do_debug:
            debug_save_barcode_crop(img_cv, "debug_crops")
        result = extract_order_id_from_barcode(img_cv)
        print(f"order_id = {result}")

    else:
        print(f"Format tidak didukung: {ext}")