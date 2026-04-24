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

def extract_text(image_path):

    resi_text = extract_shopee_resi(image_path)
    order_text = extract_shopee_order(image_path)

    result = resi_text + " " + order_text

    print(result)

    return result