import pytesseract
from pdf2image import convert_from_path
import cv2
import numpy as np
import re

def extract_resi_from_barcode_cv(img_cv):
    """Coba baca No. Resi dari barcode. img_cv = numpy array (BGR)."""
    
    # QR Code
    qr_detector = cv2.QRCodeDetector()
    data, _, _ = qr_detector.detectAndDecode(img_cv)
    if data and re.search(r'SPX', data, re.IGNORECASE):
        print(f"[QR] {repr(data)}")
        return data.strip()

    # Barcode (butuh opencv-contrib)
    try:
        bd = cv2.barcode.BarcodeDetector()
        ok, decoded_info, decoded_type, _ = bd.detectAndDecodeWithType(img_cv)
        if ok:
            for info in decoded_info:
                print(f"[Barcode] {repr(info)}")
                if info and re.search(r'SPX', info, re.IGNORECASE):
                    return info.strip()

        # Coba crop area barcode atas (15-40% height)
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        h, w = gray.shape
        crop = gray[int(h*0.15):int(h*0.40), 0:w]
        crop = cv2.resize(crop, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
        _, crop_bin = cv2.threshold(crop, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        
        # convert ke BGR untuk detector
        crop_bgr = cv2.cvtColor(crop_bin, cv2.COLOR_GRAY2BGR)
        ok2, decoded_info2, _, _ = bd.detectAndDecodeWithType(crop_bgr)
        if ok2:
            for info in decoded_info2:
                print(f"[Barcode crop] {repr(info)}")
                if info and re.search(r'SPX', info, re.IGNORECASE):
                    return info.strip()

    except AttributeError:
        print("[WARN] cv2.barcode tidak tersedia, install opencv-contrib-python")

    return None


def extract_text_pdf(pdf_path):
    images = convert_from_path(pdf_path)
    full_text = ""

    for img_pil in images:
        # Convert PIL -> OpenCV BGR
        img_cv = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)

        # === Step 1: Baca resi dari barcode ===
        resi = extract_resi_from_barcode_cv(img_cv)

        # === Step 2: OCR untuk order_id dan data lain ===
        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
        thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
        ocr_text = pytesseract.image_to_string(thresh)

        # === Step 3: Inject resi ke text kalau barcode berhasil ===
        if resi:
            # Tambahkan di depan supaya regex parser bisa nemuin
            full_text += f"No.Resi: {resi}\n" + ocr_text + "\n"
        else:
            # Fallback: andalkan OCR murni (regex di parser_shopee akan handle)
            full_text += ocr_text + "\n"

    return full_text