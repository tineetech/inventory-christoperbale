import re

def parse_shopee_items(text):
    items = []
    text = re.sub(r'\n+', '\n', text).strip()

    # Cari setelah header kalau ada
    header_match = re.search(
        r'#\s*Nama\s*Produk\s*SKU\s*Variasi\s*Qty',
        text, re.IGNORECASE
    )
    
    if header_match:
        scan_text = text[header_match.end():]
    else:
        # Fallback: scan semua baris, cari yang diawali angka
        print("[INFO] Header tidak ditemukan, fallback scan semua baris")
        scan_text = text

    lines = scan_text.strip().split('\n')

    for line in lines:
        line = line.strip()
        if not line:
            continue
        if re.match(r'Pesan\s*:', line, re.IGNORECASE):
            break

        # Match baris produk: angka di awal, qty angka di akhir
        match = re.match(
            r'^\d+\s+'
            r'(.+?)\s+'
            r'([A-Za-z0-9\-_]{2,15})\s+'
            r'(.+?)\s+'
            r'(\d+)\s*$',
            line
        )
        if match:
            items.append({
                "nama"   : match.group(1).strip(),
                "sku"    : match.group(2).strip(),
                "variasi": match.group(3).strip(),
                "qty"    : int(match.group(4)),
            })
        else:
            print(f"[SKIP] {repr(line)}")

    return items

def parse_shopee(text, items=None):
    resi     = None
    order_id = None

    resi_match = re.search(r'SPX[I1iLl|]D\s*(\d{10,})', text, re.IGNORECASE)
    if resi_match:
        raw  = resi_match.group(0).replace(" ", "")
        resi = re.sub(r'SPX[^D]*D', 'SPXID', raw, flags=re.IGNORECASE)

    order_match = re.search(r'Pesanan[: ]+([A-Z0-9\-]{10,})', text, re.IGNORECASE)
    if order_match:
        order_id = order_match.group(1)

    if items is None:
        items = []

    skus = list(dict.fromkeys(item["sku"] for item in items))

    return {
        "resi"    : resi,
        "order_id": order_id,
        "items"   : items,
        "skus"    : skus,
    }