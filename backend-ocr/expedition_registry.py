"""
expedition_registry.py — Sistem Registry Ekspedisi
====================================================
[versi patched v4]

Fix v4 vs v3:
  Bug: Resi JNE tidak terdeteksi karena:
    1. "CASHLESS" + "PENJUAL TIDAK PERLU BAYAR ONGKIR" adalah teks SHOPEE
       standar — bukan eksklusif SiCepat. Fingerprint ini menyebabkan hampir
       semua label Shopee dideteksi sebagai SiCepat, termasuk JNE.
    2. Kode hub BDO10101 (JNE) match pattern hub SiCepat [A-Z]{3}\d{5}
       karena pattern terlalu lebar.
    3. Pattern resi JNE hanya cover prefix CGK, belum cover CM, BDO, SUB,
       MLG, dan banyak kode kota JNE lainnya.

  Fix:
    1. Hapus "PENJUAL TIDAK PERLU BAYAR" dan "CASHLESS" dari
       text_fingerprints SiCepat — ini teks Shopee generik, bukan ciri khas.
    2. Perkuat hub SiCepat: digit pertama setelah 3 huruf harus '0'
       (pola real: BOO20130, CGK00001) → [A-Z]{3}0\d{4}
    3. Tambah hub_code_patterns JNE: kode-kode kota JNE yang diketahui.
    4. Perluas resi_text_patterns JNE agar cover lebih banyak prefix kota.
    5. Tambah "No. Resi: CM..." ke extract_resi_from_text agar terbaca.
"""

import re
from dataclasses import dataclass, field
from typing import Callable, Optional


@dataclass
class ExpeditionConfig:
    name                  : str
    resi_text_patterns    : list
    barcode_resi_patterns : list
    barcode_crop_zones    : list
    normalize_fn          : Optional[Callable[[str], str]] = None
    exclude_patterns      : list = field(default_factory=list)
    hub_code_patterns     : list = field(default_factory=list)
    text_fingerprints     : list = field(default_factory=list)


# ──────────────────────────────────────────────────────────────────────────────
# Fungsi normalisasi
# ──────────────────────────────────────────────────────────────────────────────

def _normalize_spx(resi: str) -> str:
    resi = resi.strip().replace(" ", "")
    resi = re.sub(r'^SPX[^D]*D', 'SPXID', resi, flags=re.IGNORECASE)
    return resi.upper()

def _normalize_sicepat(resi: str) -> str:
    return re.sub(r'\D', '', resi.strip())

def _normalize_jne(resi: str) -> str:
    return resi.strip().upper()

def _normalize_jnt(resi: str) -> str:
    return resi.strip().upper()

def _normalize_anteraja(resi: str) -> str:
    return resi.strip().upper()

def _normalize_idexpress(resi: str) -> str:
    return resi.strip().upper()

def _normalize_ninja(resi: str) -> str:
    return resi.strip().upper()

def _normalize_lion(resi: str) -> str:
    return resi.strip().upper()


# ──────────────────────────────────────────────────────────────────────────────
# REGISTRY UTAMA
# ──────────────────────────────────────────────────────────────────────────────

EXPEDITION_REGISTRY: dict[str, ExpeditionConfig] = {

    # ── 1. Shopee Express (SPX) ───────────────────────────────────────────────
    "spx": ExpeditionConfig(
        name="Shopee Express (SPX)",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+(SPX[I1iLl|]D\s*\d{10,})', re.IGNORECASE),
            re.compile(r'(SPXID\d{10,})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^SPXID\d{10,}$', re.IGNORECASE),
            re.compile(r'^SPX[I1iLl|]D\d{10,}$', re.IGNORECASE),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.30, 1.00),
            (0.00, 0.45, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_spx,
    ),

    # ── 2. AnterAja ───────────────────────────────────────────────────────────
    "anteraja": ExpeditionConfig(
        name="AnterAja",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+(\d{14})(?!\d)', re.IGNORECASE),
            re.compile(r'No\.?\s*Resi\s*[:\s]+(AA\d{10,})', re.IGNORECASE),
            re.compile(r'(AA\d{10,})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^\d{14}$'),
            re.compile(r'^AA\d{10,}$', re.IGNORECASE),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.00, 1.00),
            (0.00, 0.45, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_anteraja,
        hub_code_patterns=[
            re.compile(r'\b[A-Z]{3,5}\d\s*\|\s*[A-Z]{2,5}\b'),
        ],
        text_fingerprints=[
            "PAKEKO",
            "PAKEKO AJA",
            "PAKEKOA",
            "PAK EKO",
        ],
    ),

    # ── 3. SiCepat ────────────────────────────────────────────────────────────
    # FIX v4:
    #   - Hapus fingerprint "PENJUAL TIDAK PERLU BAYAR" dan "CASHLESS" —
    #     kedua teks ini muncul di label Shopee SEMUA ekspedisi, bukan
    #     eksklusif SiCepat. Menyebabkan false positive masif.
    #   - Perkuat hub pattern: SiCepat real → digit pertama setelah 3 huruf
    #     adalah '0' (BOO20130, CGK00001). Pattern baru: [A-Z]{3}0\d{4}
    #     sehingga BDO10101 (JNE) tidak lagi match.
    "sicepat": ExpeditionConfig(
        name="SiCepat",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+(\d{12})(?!\d)', re.IGNORECASE),
            re.compile(r'Resi\s*:\s*(\d{12})(?!\d)', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^\d{12}$'),
        ],
        barcode_crop_zones=[
            (0.00, 0.30, 0.00, 1.00),
            (0.00, 0.40, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_sicepat,
        exclude_patterns=[
            re.compile(r'^26\d{4}[A-Z0-9]{6,}$'),
        ],
        hub_code_patterns=[
            # FIX: digit pertama setelah 3 huruf HARUS '0' (pola real SiCepat)
            # BOO20130, CGK00001, BDG01234 — BUKAN BDO10101 (itu JNE)
            re.compile(r'\b[A-Z]{3}0\d{4}\b'),
        ],
        text_fingerprints=[
            # DIHAPUS: "PENJUAL TIDAK PERLU BAYAR ONGKIR" → teks Shopee generik
            # DIHAPUS: "PENJUAL TIDAK PERLU BAYAR"        → teks Shopee generik
            # DIHAPUS: "CASHLESS"                         → teks Shopee generik
            "SICEPAT",
            "SICEPA",
        ],
    ),

    # ── 4. JNE ────────────────────────────────────────────────────────────────
    # FIX v4:
    #   - Perluas resi_text_patterns: cover prefix kota JNE selain CGK.
    #     Format resi JNE: [2-3 huruf kota][8-13 digit][opsional huruf]
    #     Contoh real: CM94708765990, CGK10285432198, BDO123456789
    #   - Tambah hub_code_patterns: kode-kode kota/hub JNE yang diketahui.
    #     Format hub JNE di label: [kode kota] + angka, misal BDO10101
    "jne": ExpeditionConfig(
        name="JNE",
        resi_text_patterns=[
            # Pattern utama: 2-3 huruf + 8-13 digit (cover CM, CGK, BDO, SUB, dll)
            re.compile(r'No\.?\s*Resi\s*[:\s]+([A-Z]{2,3}\d{8,13}[A-Z0-9]*)', re.IGNORECASE),
            # Fallback langsung: CM + digit (common JNE prefix)
            re.compile(r'\bNo\.\s*Resi\s*[:\s]*(CM\d{8,})', re.IGNORECASE),
            # Barcode / teks bebas: awali CGK (paling umum)
            re.compile(r'(CGK[A-Z0-9]{8,})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^CGK[A-Z0-9]{8,}$', re.IGNORECASE),
            re.compile(r'^[A-Z]{2,3}\d{8,}[A-Z0-9]*$', re.IGNORECASE),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.00, 1.00),
            (0.00, 0.50, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_jne,
        hub_code_patterns=[
            # FIX: hub JNE dikenal dengan kode kota 2-3 huruf + 4-6 digit
            # Contoh: BDO10101, CGK12345, SUB00123, MLG00456, JOG10001
            # Bedakan dari SiCepat (digit pertama '0') dengan accept digit apapun
            re.compile(r'\b(BDO|CGK|SUB|MLG|JOG|MES|BPN|PLM|PNK|UPG|DPS|LOP|AMQ|MDC|SRG|SMG|SOC|BTH|PKU|TKG|PDG|BKS|CBN|CIK|CLP|BJM|KOE|TTE|BIK|MKS)\d{3,6}\b', re.IGNORECASE),
        ],
        text_fingerprints=[
            "JNE",
            "JALUR NUGRAHA",
            "PESANAN ANDA DIASURANSIKAN",  # teks khas JNE
        ],
    ),

    # ── 5. J&T Express ────────────────────────────────────────────────────────
    "jnt": ExpeditionConfig(
        name="J&T Express",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+(JP\d{8,})', re.IGNORECASE),
            re.compile(r'(JP\d{8,})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^JP\d{8,}$', re.IGNORECASE),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.00, 1.00),
            (0.00, 0.50, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_jnt,
    ),

    # ── 6. ID Express ─────────────────────────────────────────────────────────
    "idexpress": ExpeditionConfig(
        name="ID Express",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+(ID\d{10,})', re.IGNORECASE),
            re.compile(r'(ID\d{10,})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^ID\d{10,}$', re.IGNORECASE),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.00, 1.00),
            (0.00, 0.50, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_idexpress,
    ),

    # ── 7. Ninja Express ──────────────────────────────────────────────────────
    "ninja": ExpeditionConfig(
        name="Ninja Express",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+((?:NVSO|NV)?ID\d{10,})', re.IGNORECASE),
            re.compile(r'(NVSOID\d{8,})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^NVSOID\d{8,}$', re.IGNORECASE),
            re.compile(r'^NVSO[A-Z0-9]{8,}$', re.IGNORECASE),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.00, 1.00),
            (0.00, 0.50, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_ninja,
    ),

    # ── 8. Lion Parcel ────────────────────────────────────────────────────────
    "lion": ExpeditionConfig(
        name="Lion Parcel",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+(LC\w{8,})', re.IGNORECASE),
            re.compile(r'No\.?\s*Resi\s*[:\s]+(\d{12,15})', re.IGNORECASE),
        ],
        barcode_resi_patterns=[
            re.compile(r'^LC\w{8,}$', re.IGNORECASE),
            re.compile(r'^\d{12,15}$'),
        ],
        barcode_crop_zones=[
            (0.05, 0.35, 0.00, 1.00),
            (0.00, 0.50, 0.00, 1.00),
            (0.00, 1.00, 0.00, 1.00),
        ],
        normalize_fn=_normalize_lion,
    ),
}


# ──────────────────────────────────────────────────────────────────────────────
# Fungsi utama
# ──────────────────────────────────────────────────────────────────────────────

def detect_expedition_from_text(text: str) -> Optional[str]:
    """
    Deteksi ekspedisi dari teks PDF layer / OCR (3 lapis).

    PENTING — urutan keyword_map:
    "JNE" harus dicek SEBELUM loop hub_code/fingerprint karena teks JNE
    sering mengandung nama "JNE" secara eksplisit di label.
    """
    text_upper = text.upper()

    # ── Layer 1: Keyword teks langsung ───────────────────────────────────────
    keyword_map = {
        "spx"       : ["SHOPEE EXPRESS", "SPXID", "SPX"],
        "jne"       : ["JNE", "JALUR NUGRAHA", "PESANAN ANDA DIASURANSIKAN"],
        "jnt"       : ["J&T", "J T EXPRESS", "JT EXPRESS"],
        "anteraja"  : ["ANTERAJA", "ANTER AJA", "PAKEKO", "PAKEKOA", "PAK EKO"],
        "idexpress" : ["ID EXPRESS", "IDEXPRESS"],
        "ninja"     : ["NINJA EXPRESS", "NINJA XPRESS", "NVSO"],
        "lion"      : ["LION PARCEL", "LION EXPRESS"],
        "sicepat"   : ["SICEPAT", "SICEPA\u00C0T"],
    }
    for exp_key, keywords in keyword_map.items():
        for kw in keywords:
            if kw in text_upper:
                print(f"[Registry detect] Layer1 keyword match: {exp_key!r} via {kw!r}")
                return exp_key

    # ── Layer 2: Hub code & text fingerprints ─────────────────────────────────
    for exp_key, config in EXPEDITION_REGISTRY.items():
        for hub_pat in config.hub_code_patterns:
            if hub_pat.search(text):
                print(f"[Registry detect] Layer2 hub_code match: {exp_key!r}")
                return exp_key
        for fp in config.text_fingerprints:
            if fp.upper() in text_upper:
                print(f"[Registry detect] Layer2 fingerprint match: {exp_key!r} via {fp!r}")
                return exp_key

    # ── Layer 3: Inferensi dari format resi ───────────────────────────────────
    resi_inference = re.search(
        r'No\.?\s*Resi\s*[:\s]+([\w\d]+)', text, re.IGNORECASE
    )
    if resi_inference:
        candidate = resi_inference.group(1).strip()
        for exp_key, config in EXPEDITION_REGISTRY.items():
            for pat in config.barcode_resi_patterns:
                if pat.match(candidate):
                    excluded = any(ep.search(candidate) for ep in config.exclude_patterns)
                    if not excluded:
                        print(f"[Registry detect] Layer3 resi-format inference: {exp_key!r} "
                              f"via resi={candidate!r}")
                        return exp_key

    print("[Registry detect] Ekspedisi tidak terdeteksi dari teks")
    return None


def extract_resi_from_text(text: str, expedition_key: Optional[str] = None) -> Optional[str]:
    """
    Extract nomor resi dari teks OCR / PDF layer.
    """
    keys_to_try = [expedition_key] if expedition_key else list(EXPEDITION_REGISTRY.keys())

    for key in keys_to_try:
        config = EXPEDITION_REGISTRY.get(key)
        if not config:
            continue

        for pattern in config.resi_text_patterns:
            m = pattern.search(text)
            if m:
                resi_raw = m.group(1).strip()
                excluded = any(ep.search(resi_raw) for ep in config.exclude_patterns)
                if excluded:
                    print(f"[Registry] {key}: pattern match tapi excluded: {resi_raw!r}")
                    continue
                resi = config.normalize_fn(resi_raw) if config.normalize_fn else resi_raw
                print(f"[Registry] ✅ Resi ditemukan via text [{config.name}]: {resi!r}")
                return resi

    return None


def validate_barcode_as_resi(
    barcode_text: str,
    expedition_key: Optional[str] = None
) -> tuple[Optional[str], Optional[str]]:
    keys_to_try = [expedition_key] if expedition_key else list(EXPEDITION_REGISTRY.keys())

    for key in keys_to_try:
        config = EXPEDITION_REGISTRY.get(key)
        if not config:
            continue

        for pattern in config.barcode_resi_patterns:
            if pattern.match(barcode_text.strip()):
                excluded = any(ep.search(barcode_text) for ep in config.exclude_patterns)
                if excluded:
                    continue
                resi = config.normalize_fn(barcode_text) if config.normalize_fn else barcode_text
                print(f"[Registry] ✅ Barcode valid sebagai resi [{config.name}]: {resi!r}")
                return resi, key

    return None, None


def get_barcode_zones(expedition_key: Optional[str] = None) -> list[tuple]:
    if expedition_key and expedition_key in EXPEDITION_REGISTRY:
        return EXPEDITION_REGISTRY[expedition_key].barcode_crop_zones

    all_zones = []
    seen = set()
    for config in EXPEDITION_REGISTRY.values():
        for zone in config.barcode_crop_zones:
            if zone not in seen:
                seen.add(zone)
                all_zones.append(zone)
    return all_zones


# ──────────────────────────────────────────────────────────────────────────────
# Self-test
# ──────────────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("=" * 60)
    print("TEST detect_expedition_from_text")
    print("=" * 60)

    # Teks dari PDF URBAN_84_SHP_150526.pdf (JNE Reguler Shopee)
    jne_label_text = """
BDO10101 No. Resi: CM94708765990
Penerima:Diah Sabariah    Pengirim: Urban Foot Step
HOME                      6281282127888
Perumahan Kota baru arjasari blok a3/10 rt. 00  KAB. BOGOR
4 rw. 13, ARJASARI, KAB. BANDUNG, JAWA BARAT
KAB. BANDUNG              ARJASARI
CASHLESS    Penjual tidak perlu bayar ongkir ke Kurir
Berat: 850 gr  COD: Rp0
Batas Kirim: 15-05-2026
No. Pesanan: 260515EUQ8R5TY
# Nama Produk   SKU       Variasi  Qty
1 Christian Bale CAMELLIA sandal kulit flatform wanita ban 3 premium original
  cmlcrm37      CREAM,37  1
Pesan: (260515EUQ8R5TY)
"""

    test_cases = [
        # (text, expected_expedition, expected_resi, description)
        (jne_label_text, "jne", "CM94708765990", "JNE via hub BDO10101 + resi CM"),
        ("SPXID067214182654 No. Resi: SPXID067214182654", "spx", "SPXID067214182654", "SPX via keyword"),
        ("No. Resi: 004607855558\nBOO20130", "sicepat", "004607855558", "SiCepat via hub BOO20130"),
        ("PAKEKO\nNo. Resi: 11003785760273", "anteraja", "11003785760273", "AnterAja via PAKEKO"),
        # Pastikan CASHLESS saja tidak lagi trigger SiCepat
        ("CASHLESS Penjual tidak perlu bayar ongkir ke Kurir\nNo. Resi: 004607855558", "sicepat", "004607855558", "SiCepat resi 12 digit (CASHLESS bukan trigger)"),
    ]

    all_pass = True
    for text, exp_exp, exp_resi, desc in test_cases:
        detected_exp = detect_expedition_from_text(text)
        detected_resi = extract_resi_from_text(text, detected_exp)
        ok_exp  = detected_exp  == exp_exp
        ok_resi = detected_resi == exp_resi
        ok = ok_exp and ok_resi
        if not ok: all_pass = False
        print(f"{'✅' if ok else '❌'} {desc}")
        if not ok_exp:
            print(f"   expedition got={detected_exp!r} expected={exp_exp!r}")
        if not ok_resi:
            print(f"   resi      got={detected_resi!r} expected={exp_resi!r}")

    print("=" * 60)
    print(f"{'✅ SEMUA PASS' if all_pass else '❌ ADA YANG GAGAL'}")