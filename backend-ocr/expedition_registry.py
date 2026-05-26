"""
expedition_registry.py — Sistem Registry Ekspedisi
====================================================
[versi patched v5]

Fix v5 vs v4:
  Tambah fungsi process_label(text, ekspedisi_mode) sebagai entry point utama.

  - ekspedisi_mode diisi (misal "jne", "sicepat") → skip auto-detect Layer 1/2/3,
    langsung proses pakai registry ekspedisi yang dimaksud.
    Menghilangkan seluruh risiko false-positive antar ekspedisi.
  - ekspedisi_mode = None → fallback ke auto-detect seperti v4.

  Contoh pemanggilan dari FastAPI task:
    exp, resi = process_label(pdf_text, ekspedisi_mode="jne")
    exp, resi = process_label(pdf_text)  # auto-detect
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
            re.compile(r'\b[A-Z]{3}0\d{4}\b'),
        ],
        text_fingerprints=[
            "SICEPAT",
            "SICEPA",
        ],
    ),

    # ── 4. JNE ────────────────────────────────────────────────────────────────
    "jne": ExpeditionConfig(
        name="JNE",
        resi_text_patterns=[
            re.compile(r'No\.?\s*Resi\s*[:\s]+([A-Z]{2,3}\d{8,13}[A-Z0-9]*)', re.IGNORECASE),
            re.compile(r'\bNo\.\s*Resi\s*[:\s]*(CM\d{8,})', re.IGNORECASE),
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
            re.compile(
                r'\b(BDO|CGK|SUB|MLG|JOG|MES|BPN|PLM|PNK|UPG|DPS|LOP|AMQ|MDC|'
                r'SRG|SMG|SOC|BTH|PKU|TKG|PDG|BKS|CBN|CIK|CLP|BJM|KOE|TTE|BIK|MKS)'
                r'\d{3,6}\b',
                re.IGNORECASE,
            ),
        ],
        text_fingerprints=[
            "JNE",
            "JALUR NUGRAHA",
            "PESANAN ANDA DIASURANSIKAN",
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

# Alias yang diterima dari luar (form param) → kunci registry
# Berguna kalau frontend kirim "jne reguler", "jnt", "j&t", dsb.
EKSPEDISI_ALIAS: dict[str, str] = {
    # SPX
    "spx"              : "spx",
    "shopee express"   : "spx",
    "shopee"           : "spx",
    # AnterAja
    "anteraja"         : "anteraja",
    "anter aja"        : "anteraja",
    # SiCepat
    "sicepat"          : "sicepat",
    "si cepat"         : "sicepat",
    # JNE
    "jne"              : "jne",
    "jne reguler"      : "jne",
    "jne yes"          : "jne",
    "jne oke"          : "jne",
    # J&T
    "jnt"              : "jnt",
    "j&t"              : "jnt",
    "jt"               : "jnt",
    "j&t express"      : "jnt",
    # ID Express
    "idexpress"        : "idexpress",
    "id express"       : "idexpress",
    # Ninja
    "ninja"            : "ninja",
    "ninja express"    : "ninja",
    "ninja xpress"     : "ninja",
    # Lion
    "lion"             : "lion",
    "lion parcel"      : "lion",
}


def resolve_ekspedisi_mode(raw: Optional[str]) -> Optional[str]:
    """
    Normalisasi string ekspedisi_mode dari input luar (Form, query param, dsb)
    ke kunci registry. Return None kalau tidak dikenali.

    Contoh:
        resolve_ekspedisi_mode("JNE Reguler") → "jne"
        resolve_ekspedisi_mode("J&T")         → "jnt"
        resolve_ekspedisi_mode(None)           → None
        resolve_ekspedisi_mode("xyz")          → None  (tidak dikenal)
    """
    if not raw:
        return None
    key = raw.strip().lower()
    return EKSPEDISI_ALIAS.get(key)


# ──────────────────────────────────────────────────────────────────────────────
# Fungsi utama
# ──────────────────────────────────────────────────────────────────────────────

def process_label(
    text: str,
    ekspedisi_mode: Optional[str] = None,
) -> tuple[Optional[str], Optional[str]]:
    """
    Entry point utama untuk deteksi ekspedisi + ekstrak resi.

    Parameters
    ----------
    text : str
        Teks PDF layer atau hasil OCR dari satu halaman label.
    ekspedisi_mode : str | None
        Kunci ekspedisi yang sudah diketahui ("jne", "sicepat", dst).
        Bisa raw string dari form — akan dinormalisasi via resolve_ekspedisi_mode.
        Kalau None → fallback ke auto-detect 3-layer seperti v4.

    Returns
    -------
    (expedition_key, resi) — salah satu atau keduanya bisa None kalau tidak ketemu.

    Contoh
    ------
    # Mode eksplisit — tidak ada detection sama sekali, paling aman
    exp, resi = process_label(pdf_text, ekspedisi_mode="jne")

    # Terima raw string dari Form FastAPI langsung
    exp, resi = process_label(pdf_text, ekspedisi_mode="JNE Reguler")

    # Auto-detect (fallback, sama seperti v4)
    exp, resi = process_label(pdf_text)
    """
    # Normalisasi input (handle raw string dari Form)
    resolved = resolve_ekspedisi_mode(ekspedisi_mode)

    if resolved:
        print(f"[process_label] Mode eksplisit: {resolved!r} "
              f"(raw input: {ekspedisi_mode!r}) — skip auto-detect")
        resi = extract_resi_from_text(text, expedition_key=resolved)
        return resolved, resi

    # Fallback: auto-detect
    if ekspedisi_mode is not None:
        # Input diberikan tapi tidak dikenal di alias — log warning
        print(f"[process_label] WARNING: ekspedisi_mode {ekspedisi_mode!r} "
              f"tidak dikenal, fallback ke auto-detect")

    detected = detect_expedition_from_text(text)
    resi = extract_resi_from_text(text, detected) if detected else None
    return detected, resi


def detect_expedition_from_text(text: str) -> Optional[str]:
    """
    Deteksi ekspedisi dari teks PDF layer / OCR (3 lapis).
    Gunakan process_label() sebagai entry point bila ekspedisi sudah diketahui.
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
        "sicepat"   : ["SICEPAT", "SICEPAT"],
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
    print("TEST process_label — mode eksplisit")
    print("=" * 60)

    jne_label_text = """
CGK10301 No. Resi: CM21075383872
Penerima:Sawali    Pengirim: Agatha Lumiere Official
KOTA JAKARTA PUSAT
CASHLESS    Penjual tidak perlu bayar ongkir ke Kurir
Berat: 700 gr  COD: Rp0
No. Pesanan: 260525BJ4PE66A
"""

    sicepat_label_text = """
BOO20130 No. Resi: 004607855558
Penerima: Budi    Pengirim: Toko ABC
SICEPAT REG
"""

    test_cases = [
        # (text, ekspedisi_mode, expected_exp, expected_resi, desc)
        (jne_label_text,     "jne",     "jne",     "CM21075383872", "JNE mode eksplisit — skip SiCepat false-positive"),
        (jne_label_text,     "JNE",     "jne",     "CM21075383872", "JNE mode eksplisit — uppercase raw input"),
        (jne_label_text,     "JNE Reguler", "jne", "CM21075383872", "JNE mode eksplisit — raw form string"),
        (jne_label_text,     None,      "jne",     "CM21075383872", "JNE auto-detect via hub CGK10301"),
        (sicepat_label_text, "sicepat", "sicepat", "004607855558",  "SiCepat mode eksplisit"),
        (sicepat_label_text, None,      "sicepat", "004607855558",  "SiCepat auto-detect via SICEPAT keyword"),
        # CASHLESS saja tidak boleh trigger SiCepat
        ("CASHLESS Penjual tidak perlu bayar ongkir\nNo. Resi: 004607855558",
         None, "sicepat", "004607855558", "SiCepat resi 12 digit (CASHLESS bukan trigger)"),
    ]

    all_pass = True
    for text, mode, exp_exp, exp_resi, desc in test_cases:
        got_exp, got_resi = process_label(text, ekspedisi_mode=mode)
        ok = (got_exp == exp_exp) and (got_resi == exp_resi)
        if not ok:
            all_pass = False
        print(f"{'✅' if ok else '❌'} {desc}")
        if got_exp != exp_exp:
            print(f"   expedition got={got_exp!r} expected={exp_exp!r}")
        if got_resi != exp_resi:
            print(f"   resi      got={got_resi!r} expected={exp_resi!r}")

    print("=" * 60)
    print(f"{'✅ SEMUA PASS' if all_pass else '❌ ADA YANG GAGAL'}")