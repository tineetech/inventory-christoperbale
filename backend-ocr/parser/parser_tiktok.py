import re

def parse_tiktok(text):

    resi = None
    order_id = None

    resi_match = re.search(r'JX\d{10}', text)
    if resi_match:
        resi = resi_match.group()

    order_match = re.search(r'Order\s*ID[: ]+(\d+)', text)
    if order_match:
        order_id = order_match.group(1)

    return {
        "resi": resi,
        "order_id": order_id
    }