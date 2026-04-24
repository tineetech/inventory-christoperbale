import re

# parser_shopee.py

def parse_shopee(text):
    resi = None
    order_id = None

    # Pattern SPXID - lebih toleran terhadap noise OCR
    # Handle: SPXID, SPX1D, SPXLD, SPX|D, dll
    resi_match = re.search(r'SPX[I1iLl|]D\s*(\d{10,})', text, re.IGNORECASE)
    if resi_match:
        # Gabungkan prefix + angka, normalize karakter mirip
        raw = resi_match.group(0).replace(" ", "")
        # Normalize karakter yang sering salah baca di posisi ke-4
        resi = re.sub(r'SPX[^D]*D', 'SPXID', raw, flags=re.IGNORECASE)

    # Order ID
    order_match = re.search(r'Pesanan[: ]+([A-Z0-9\-]{10,})', text, re.IGNORECASE)
    if order_match:
        order_id = order_match.group(1)

    return {
        "resi": resi,
        "order_id": order_id
    }