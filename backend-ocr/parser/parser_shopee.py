import re


def parse_shopee_items(text: str) -> list[dict]:
    """
    Parse tabel item dari teks OCR resi Shopee.
    Mendukung nama produk yang multi-baris (wrap).
    """
    items = []
    text = re.sub(r'\n+', '\n', text).strip()

    # --- 1. Cari header tabel ---
    header_match = re.search(
        r'#\s*Nama\s*Produk\s*SKU\s*Variasi\s*Qty',
        text, re.IGNORECASE
    )
    scan_text = text[header_match.end():] if header_match else text

    # --- 2. Potong di sentinel "Pesan:" ---
    sentinel = re.search(r'^Pesan\s*:', scan_text, re.IGNORECASE | re.MULTILINE)
    if sentinel:
        scan_text = scan_text[:sentinel.start()]

    lines = [l.strip() for l in scan_text.split('\n')]
    lines = [l for l in lines if l]

    # --- 3. Kelompokkan baris per item ---
    groups: list[list[str]] = []
    for line in lines:
        if re.match(r'^\d+\s+\S', line):
            groups.append([line])
        elif groups:
            groups[-1].append(line)

    # --- 4. Parse tiap grup ---
    TAIL_RE = re.compile(
        r'([a-z][a-z0-9\-_]{2,19})'   # SKU: lowercase only (Shopee SKU selalu lowercase)
        r'\s+'
        r'(.+?)'
        r'\s+'
        r'(\d+)\s*$'
    )

    for group in groups:
        full = ' '.join(group)
        full = re.sub(r'^\d+\s+', '', full).strip()

        tail_match = TAIL_RE.search(full)
        if not tail_match:
            print(f"[SKIP grup] {repr(group)}")
            continue

        sku     = tail_match.group(1).strip()
        variasi = tail_match.group(2).strip()
        qty     = int(tail_match.group(3))
        nama    = full[:tail_match.start()].strip()
        nama    = re.sub(r'[\s\-]+$', '', nama)

        # ── FIX: buang SKU yang nyasar ke nama ──────────────────────
        # SKU Shopee selalu lowercase alfanumerik, misal: jovcrm40, jespt39
        # Kalau ada token lowercase+digit di akhir nama, itu SKU yang nyasar
        nama_tokens = nama.split()
        cleaned_nama = []
        extra_sku = ""
        for token in nama_tokens:
            # Token pure lowercase+digit panjang 4-20 char → kemungkinan SKU
            if re.fullmatch(r'[a-z][a-z0-9\-_]{3,19}', token):
                extra_sku = token  # simpan, override sku jika sku kosong
            else:
                cleaned_nama.append(token)
        
        if extra_sku and not sku:
            sku = extra_sku
        elif extra_sku:
            # Ada dua kandidat SKU — yang di TAIL_RE lebih reliable
            pass
        nama = ' '.join(cleaned_nama).strip()
        if not nama:
            nama_lines = group[:-1] if len(group) > 1 else group
            nama = ' '.join(nama_lines)
            nama = re.sub(r'^\d+\s+', '', nama).strip()

        items.append({
            "nama"   : nama,
            "sku"    : sku,
            "variasi": variasi,
            "qty"    : qty,
        })

    return items


# ================================================================
# FIX 1: Normalisasi OCR noise pada 6 digit tanggal nomor pesanan
# ================================================================
def _normalize_order_date_prefix(order_id: str) -> str:
    """
    Shopee order ID diawali 6 digit tanggal (YYMMDD) — selalu angka.
    OCR sering salah baca angka sebagai huruf di bagian ini.
    Koreksi hanya pada 6 karakter pertama karena posisi sisanya
    bisa berupa huruf yang memang benar (tidak bisa dibedakan).
    """
    if not order_id or len(order_id) < 6:
        return order_id

    date_part = order_id[:6]
    rest      = order_id[6:]

    # Karakter yang OCR salah baca sebagai angka di posisi tanggal
    date_fixed = (date_part
        .replace('O', '0').replace('o', '0')   # O → nol
        .replace('I', '1').replace('l', '1')    # I/l → satu
        .replace('|', '1')                       # pipe → satu
        .replace('S', '5').replace('s', '5')    # S → lima (hanya di zona tanggal)
        .replace('G', '6').replace('g', '6')    # G → enam
        .replace('B', '8')                       # B → delapan
        .replace('Z', '2').replace('z', '2')    # Z → dua
        .replace('A', '4')                       # A → empat
    )

    # Safety: kalau setelah replacement masih ada huruf, jangan koreksi
    # (artinya OCR sangat kacau, lebih baik biarkan user edit manual)
    if not date_fixed.isdigit():
        return order_id

    return date_fixed + rest


def parse_shopee(text: str, items: list[dict] | None = None) -> dict:
    resi     = None
    order_id = None

    # --- Nomor resi SPXID ---
    resi_match = re.search(r'SPX[I1iLl|]D\s*(\d{10,})', text, re.IGNORECASE)
    if resi_match:
        raw  = resi_match.group(0).replace(" ", "")
        resi = re.sub(r'SPX[^D]*D', 'SPXID', raw, flags=re.IGNORECASE)

    # ================================================================
    # FIX 2: Pattern no. pesanan yang lebih lengkap
    #
    # Format yang ditemukan di PDF Shopee:
    #   (A) No.Pesanan: 260425QRVRRW7D        ← format standar
    #   (B) Pesan: (260425QM2KDD1Q)           ← format dengan kurung ← BUG LAMA
    #   (C) Pesanan: 260426TKU5NY5Y           ← tanpa prefix "No."
    #   (D) No. Pesanan : 260426SN0M6DNM      ← dengan spasi sebelum titik dua
    # ================================================================
    order_match = re.search(
        r'(?:'
        r'No\.?\s*Pesanan\s*[:\s]+([A-Z0-9]{10,})'     # Format A & D
        r'|'
        r'Pesan(?:an)?\s*[:\s]+\(?([A-Z0-9]{10,})\)?'  # Format B & C
        r')',
        text,
        re.IGNORECASE
    )
    if order_match:
        raw_order = order_match.group(1) or order_match.group(2)
        # Normalisasi OCR noise pada 6 digit tanggal
        order_id = _normalize_order_date_prefix(raw_order.upper())

    if items is None:
        items = parse_shopee_items(text)

    skus = [item["sku"] for item in items]

    return {
        "resi"    : resi,
        "order_id": order_id,
        "items"   : items,
        "skus"    : skus,
    }


# ── Quick test ────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    # Semua format dari PDF riil
    test_cases = [
        # (text_snippet, expected_order_id, description)
        ("Pesan: (260425QM2KDD1Q)",      "260425QM2KDD1Q", "format Pesan: (...)"),
        ("No.Pesanan: 260425QRVRRW7D",   "260425QRVRRW7D", "format No.Pesanan"),
        ("No.Pesanan: 260425R19TG85E",   "260425R19TG85E", "No.Pesanan normal"),
        ("No.Pesanan: 260426SN0M6DNM",   "260426SN0M6DNM", "No.Pesanan dengan nol"),
        ("No.Pesanan: 260426T1BSW6RM",   "260426T1BSW6RM", "No.Pesanan dengan 1"),
        ("No.Pesanan: 260426TKU5NY5Y",   "260426TKU5NY5Y", "No.Pesanan dengan 5"),
        # Simulasi OCR error pada tanggal (bisa dikoreksi)
        ("No.Pesanan: 26O425QRVRRW7D",   "260425QRVRRW7D", "O→0 di tanggal"),
        ("No.Pesanan: 2GO425R19TG85E",   "260425R19TG85E", "G→6 di tanggal"),
        # OCR error pada bagian alfanumerik (TIDAK bisa dikoreksi otomatis)
        # → user harus edit manual
        ("No.Pesanan: 260426SNOM6DNM",   "260426SNOM6DNM", "O di alfanum → tidak dikoreksi (user edit)"),
        ("No.Pesanan: 260426TKUSNY5Y",   "260426TKUSNY5Y", "S di alfanum → tidak dikoreksi (user edit)"),
    ]

    print("=" * 60)
    all_pass = True
    for text, expected, desc in test_cases:
        result = parse_shopee(text)
        got    = result["order_id"]
        ok     = got == expected
        if not ok: all_pass = False
        print(f"{'✅' if ok else '❌'} {desc}")
        if not ok:
            print(f"   got:      {repr(got)}")
            print(f"   expected: {repr(expected)}")

    print("=" * 60)
    print(f"{'✅ SEMUA PASS' if all_pass else '❌ ADA YANG GAGAL'}")

    print()
    print("--- Test item parsing ---")
    sample = """
# Nama Produk SKU Variasi Qty
1 Christian Bale Jessica Sendal flat
  platform Cewek silang cantik sandal
  Wanita
  jespt39 Putih,39 1
Pesan: (260425QM2KDD1Q)
"""
    items = parse_shopee_items(sample)
    for item in items:
        print(item)