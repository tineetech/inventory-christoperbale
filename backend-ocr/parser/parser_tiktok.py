import re

def parse_tiktok(text, items=None):
    resi     = None
    order_id = None

    # Resi J&T: JX + 10 digit
    resi_match = re.search(r'JX\d{10}', text)
    if resi_match:
        resi = resi_match.group()

    # Order ID TikTok: angka panjang setelah "Order Id" atau "Order ID"
    order_match = re.search(r'Order\s*I[dD]\s*[:\s]+(\d{15,})', text)
    if order_match:
        order_id = order_match.group(1)

    if items is None:
        items = []

    skus = [item["sku"] for item in items]

    return {
        "resi"    : resi,
        "order_id": order_id,
        "items"   : items,
        "skus"    : skus,
    }