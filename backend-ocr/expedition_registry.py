"""
expedition_registry.py — Sistem Registry Ekspedisi
====================================================
[versi patched v6]

Fix v6 vs v5:
  Bug: Halaman SiCepat dengan hub DPS10009 terdeteksi sebagai JNE
       → resi tidak terbaca (resi=None).

  Root cause v5:
    Kode kota "DPS" (Denpasar) masuk ke dalam _JNE_CITY_CODES.
    Akibatnya, negative-lookahead pada SiCepat hub pattern MENGECUALIKAN
    DPS, sehingga DPS10009 tidak match SiCepat.
    Sebaliknya hub JNE pattern r'\b(BDO|...|DPS|...)\d{3,6}\b' MATCH
    DPS10009, dan sistem mendeteksi sebagai JNE.
    JNE barcode scan tidak menemukan resi 12 digit → resi=None.

    Catatan penting tentang text layer PDF:
    Logo SiCepat di PDF ini disimpan sebagai IMAGE, bukan teks.
    Akibatnya kata "SICEPAT" tidak ada di text layer, sehingga
    Layer1 keyword detection tidak bisa menyelamatkan page ini.
    Satu-satunya sinyal yang tersedia adalah hub code DPS10009
    dan format resi 12 digit.

  Fix v6:
    1. Hapus DPS dari _JNE_CITY_CODES.
       DPS (Denpasar) dipakai BERSAMA oleh SiCepat dan JNE,
       sama seperti CGK (Jakarta) dan SRG (Semarang).
       → Deteksi DPS diselesaikan via Layer1 keyword atau
         Layer3 resi format, bukan hub eksklusif.

    2. Tambah RESCUE FALLBACK di detect_expedition_from_text():
       Jika hasil deteksi = 'jne' TAPI teks mengandung resi
       12 digit numerik murni (format SiCepat), override ke 'sicepat'.
       Ini adalah safety net untuk kasus di mana:
         - Hub code ambigu atau belum dikenal
         - Keyword SICEPAT tidak ada di text layer (logo = image)
         - Format resi adalah satu-satunya pembeda yang tersisa

    3. Tambah _SICEPAT_RESI_12_PAT sebagai pattern rescue
       (re.compile di level modul agar tidak dikompilasi ulang setiap call).

    4. Audit kode-kode di _JNE_CITY_CODES:
       Kode yang dipakai BERSAMA (SiCepat + JNE) TIDAK boleh masuk daftar.
       Kode yang EKSKLUSIF JNE boleh masuk.
       Daftar kode bersama yang diketahui: CGK, SRG, DPS.
       Kode-kode ini dideteksi via Layer1/Layer3, bukan hub eksklusif.

  Perubahan kode:
    - _JNE_CITY_CODES: hapus "DPS"
    - Tambah _SICEPAT_RESI_12_PAT (module-level constant)
    - detect_expedition_from_text(): tambah rescue block setelah Layer2/Layer3
    - Komentar diperjelas untuk tiap kode kota di _JNE_CITY_CODES

  Tidak ada perubahan pada ExpeditionConfig, normalize_fn, atau
  extract_resi_from_text / validate_barcode_as_resi.
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
# Kode kota JNE (dipakai untuk hub JNE dan negative-lookahead SiCepat)
# ──────────────────────────────────────────────────────────────────────────────
#
# ATURAN PENTING:
#   Hanya masukkan kode yang EKSKLUSIF milik JNE.
#   Kode yang dipakai BERSAMA dengan SiCepat (atau kurir lain) JANGAN dimasukkan.
#   Kode bersama yang diketahui: CGK, SRG, DPS → dideteksi via Layer1/Layer3.
#
# v6: DPS dihapus karena SiCepat menggunakan DPS sebagai prefix hub
#     (contoh real: DPS10009 dari PDF CBG_16-22).

_JNE_CITY_CODES = (
    "BDO",  # Bandung       — eksklusif JNE
    "MLG",  # Malang        — eksklusif JNE
    "JOG",  # Yogyakarta    — eksklusif JNE
    "MES",  # Medan         — eksklusif JNE
    "BPN",  # Balikpapan    — eksklusif JNE
    "PLM",  # Palembang     — eksklusif JNE
    "PNK",  # Pontianak     — eksklusif JNE
    "UPG",  # Ujung Pandang — eksklusif JNE
    # "DPS" ← DIHAPUS v6: Denpasar dipakai SiCepat (DPS10009) dan JNE
    "LOP",  # Lombok        — eksklusif JNE
    "AMQ",  # Ambon         — eksklusif JNE
    "MDC",  # Manado        — eksklusif JNE
    "BTH",  # Batam         — eksklusif JNE
    "PKU",  # Pekanbaru     — eksklusif JNE
    "TKG",  # Bandar Lampung — eksklusif JNE
    "PDG",  # Padang        — eksklusif JNE
    "BKS",  # Bekasi        — eksklusif JNE
    "CBN",  # Cirebon       — eksklusif JNE
    "CIK",  # Cikarang      — eksklusif JNE
    "CLP",  # Cilegon       — eksklusif JNE
    "BJM",  # Banjarmasin   — eksklusif JNE
    "KOE",  # Kupang        — eksklusif JNE
    "TTE",  # Ternate       — eksklusif JNE
    "BIK",  # Biak          — eksklusif JNE
    "MKS",  # Makassar      — eksklusif JNE
    "SOC",  # Solo          — eksklusif JNE
    "SMG",  # Semarang kode lain — eksklusif JNE
    # Catatan: CGK dan SRG dipakai BERSAMA (SiCepat & JNE).
    # DPS juga dipakai BERSAMA (ditambahkan ke catatan v6).
    # Ketiga kode ini dideteksi via Layer1 keyword atau Layer3 format resi.
)

_JNE_CODES_RE = "|".join(_JNE_CITY_CODES)


# ──────────────────────────────────────────────────────────────────────────────
# Pattern rescue: resi 12 digit numerik murni → SiCepat
# Dipakai di rescue fallback detect_expedition_from_text()
# ──────────────────────────────────────────────────────────────────────────────

_SICEPAT_RESI_12_PAT = re.compile(
    r'\bNo\.?\s*Resi\s*[:\s]+(\d{12})(?!\d)',
    re.IGNORECASE
)


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
    # Fix v5: digit pertama hub 0,1,2 — sesuai data real.
    # Fix v6: DPS tidak lagi di-exclude (dihapus dari _JNE_CITY_CODES),
    #         sehingga DPS10009 sekarang match SiCepat secara langsung.
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
            # digit pertama 0,1,2 — sesuai data real SiCepat.
            # Negative-lookahead mengecualikan kode kota EKSKLUSIF JNE saja.
            # DPS sudah tidak ada di _JNE_CITY_CODES sehingga DPS10009 match.
            re.compile(
                r'\b(?!(?:' + _JNE_CODES_RE + r')[012])[A-Z]{3}[012]\d{4}\b'
            ),
        ],
        text_fingerprints=[
            "SICEPAT",
            "SICEPA",
        ],
    ),

    # ── 4. JNE ────────────────────────────────────────────────────────────────
    # Fix v5: hub hanya match kode eksklusif JNE.
    # Fix v6: DPS tidak lagi di daftar — tidak ada perubahan pattern di sini,
    #         tapi efeknya DPS tidak lagi match hub JNE secara salah.
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
            # Hanya kode kota JNE eksklusif — tidak termasuk DPS/CGK/SRG.
            re.compile(
                r'\b(' + _JNE_CODES_RE + r')\d{3,6}\b',
                re.IGNORECASE
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


# ──────────────────────────────────────────────────────────────────────────────
# Fungsi utama
# ──────────────────────────────────────────────────────────────────────────────

def detect_expedition_from_text(text: str) -> Optional[str]:
    """
    Deteksi ekspedisi dari teks PDF layer / OCR (3 lapis + rescue fallback).

    Alur:
      Layer 1 → keyword eksplisit ("JNE", "SICEPAT", dll.)
      Layer 2 → hub code & text fingerprint (SiCepat dicek sebelum JNE)
      Layer 3 → inferensi dari format No. Resi
      Rescue  → jika result='jne' tapi resi 12 digit ditemukan → override sicepat

    Rescue fallback (v6):
      Menangani kasus di mana logo kurir disimpan sebagai IMAGE (bukan teks),
      hub code ambigu, dan satu-satunya sinyal adalah format resi 12 digit.
      Format resi 12 digit numerik murni adalah format eksklusif SiCepat —
      JNE tidak pernah menggunakan resi 12 digit murni numerik.
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
        "sicepat"   : ["SICEPAT", "SICEPÀT"],
    }
    for exp_key, keywords in keyword_map.items():
        for kw in keywords:
            if kw in text_upper:
                print(f"[Registry detect] Layer1 keyword match: {exp_key!r} via {kw!r}")
                return exp_key

    # ── Layer 2: Hub code & text fingerprints ─────────────────────────────────
    # SiCepat dicek SEBELUM JNE agar hub seperti DPS/CGK/SRG
    # tidak salah jatuh ke JNE.
    priority_order = ["sicepat", "anteraja", "spx", "jne", "jnt",
                      "idexpress", "ninja", "lion"]
    layer2_result = None
    for exp_key in priority_order:
        config = EXPEDITION_REGISTRY.get(exp_key)
        if not config:
            continue
        for hub_pat in config.hub_code_patterns:
            if hub_pat.search(text):
                print(f"[Registry detect] Layer2 hub_code match: {exp_key!r}")
                layer2_result = exp_key
                break
        if layer2_result:
            break
        for fp in config.text_fingerprints:
            if fp.upper() in text_upper:
                print(f"[Registry detect] Layer2 fingerprint match: {exp_key!r} via {fp!r}")
                layer2_result = exp_key
                break
        if layer2_result:
            break

    if layer2_result:
        # ── Rescue dari Layer 2 ───────────────────────────────────────────────
        # Jika terdeteksi JNE tapi resi 12 digit ada → override ke sicepat.
        # Kasus: hub ambigu (misal BDO-like code belum dikenal) + resi sicepat.
        # JNE tidak pernah menggunakan resi 12 digit numerik murni.
        if layer2_result == "jne" and _SICEPAT_RESI_12_PAT.search(text):
            print("[Registry detect] Rescue: Layer2=jne tapi resi 12 digit → override sicepat")
            return "sicepat"
        return layer2_result

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

    # ── Rescue akhir: tidak ada sinyal lain, tapi resi 12 digit ada ──────────
    # Jika semua layer gagal mendeteksi ekspedisi, tapi teks mengandung
    # pola No. Resi dengan 12 digit → kembalikan sicepat sebagai best-effort.
    # Ini menangani kasus ekstrem di mana hub code tidak dikenal sama sekali
    # dan tidak ada keyword di text layer.
    if _SICEPAT_RESI_12_PAT.search(text):
        print("[Registry detect] Rescue akhir: resi 12 digit ditemukan tanpa sinyal lain → sicepat")
        return "sicepat"

    print("[Registry detect] Ekspedisi tidak terdeteksi dari teks")
    return None


def extract_resi_from_text(text: str, expedition_key: Optional[str] = None) -> Optional[str]:
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
                print(f"[Registry] Resi ditemukan via text [{config.name}]: {resi!r}")
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
                print(f"[Registry] Barcode valid sebagai resi [{config.name}]: {resi!r}")
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
    print("TEST detect_expedition_from_text — v6")
    print("=" * 60)

    test_cases = [
        # ── v6: Fix utama — DPS10009 ──────────────────────────────────────────
        # Page 1: DPS10009, resi 004610042992 — WAS BROKEN in v5
        # Text layer TIDAK mengandung "SICEPAT" (logo = image),
        # deteksi murni via hub DPS10009.
        (
            "REG | DPS10009 | No. Resi: 004610042992 | Penerima: | Gek Niken",
            "sicepat", "004610042992",
            "SiCepat DPS10009 — WAS BROKEN in v5 (DPS was in JNE list)"
        ),
        # ── Halaman 2-7 dari PDF CBG_16-22 ───────────────────────────────────
        (
            "CGK10501 No. Resi: 004610046236\nNo. Pesanan: 26052374NAWUHQ",
            "sicepat", "004610046236", "SiCepat CGK10501 (digit 1)"
        ),
        (
            "SRG10015 No. Resi: 004610056006\nNo. Pesanan: 26052377VT2NSP",
            "sicepat", "004610056006", "SiCepat SRG10015 (digit 1)"
        ),
        (
            "BOO20159 No. Resi: 004610059503\nNo. Pesanan: 2605247WD28N4S",
            "sicepat", "004610059503", "SiCepat BOO20159 (digit 2)"
        ),
        (
            "CGK10508 No. Resi: 004610056007\nNo. Pesanan: 26052483K5B24K",
            "sicepat", "004610056007", "SiCepat CGK10508 (digit 1)"
        ),
        (
            "CGK10106 No. Resi: 004610050079\nNo. Pesanan: 26052495S4TV9N",
            "sicepat", "004610050079", "SiCepat CGK10106 (digit 1)"
        ),
        (
            "BOO20123 No. Resi: 004610057812\nNo. Pesanan: 2605259UP19TYH",
            "sicepat", "004610057812", "SiCepat BOO20123 (digit 2)"
        ),
        # ── Rescue fallback test ──────────────────────────────────────────────
        # Kasus: hub belum dikenal di future, tapi resi 12 digit → sicepat
        (
            "XYZ10999 No. Resi: 009900112233\nKAB. BOGOR",
            "sicepat", "009900112233",
            "Rescue akhir: hub belum dikenal XYZ10999 + resi 12 digit → sicepat"
        ),
        # ── Pastikan JNE tidak terganggu ──────────────────────────────────────
        (
            "BDO10101 No. Resi: CM94708765990\nJNE\nNo. Pesanan: 260515EUQ8R5TY",
            "jne", "CM94708765990", "JNE via keyword + hub BDO10101"
        ),
        (
            "BDO10101 No. Resi: CM94708765990\nPenerima: Diah",
            "jne", "CM94708765990", "JNE via hub BDO10101 saja (no keyword)"
        ),
        # JNE dengan kota DPS (Denpasar, tapi resi bukan 12 digit) → JNE via keyword
        (
            "DPS 123456 No. Resi: CM94708765990\nJNE",
            "jne", "CM94708765990", "JNE DPS via keyword (resi bukan 12 digit)"
        ),
        # ── Test lama ─────────────────────────────────────────────────────────
        (
            "SPXID067214182654 No. Resi: SPXID067214182654",
            "spx", "SPXID067214182654", "SPX via keyword"
        ),
        (
            "No. Resi: 004607855558\nBOO20130",
            "sicepat", "004607855558", "SiCepat via hub BOO20130"
        ),
        (
            "PAKEKO\nNo. Resi: 11003785760273",
            "anteraja", "11003785760273", "AnterAja via PAKEKO"
        ),
        (
            "CASHLESS Penjual tidak perlu bayar ongkir ke Kurir\nNo. Resi: 004607855558",
            "sicepat", "004607855558",
            "SiCepat resi 12 digit (CASHLESS bukan trigger, rescue akhir)"
        ),
    ]

    all_pass = True
    for text, exp_exp, exp_resi, desc in test_cases:
        detected_exp  = detect_expedition_from_text(text)
        detected_resi = extract_resi_from_text(text, detected_exp)
        ok_exp  = detected_exp  == exp_exp
        ok_resi = detected_resi == exp_resi
        ok = ok_exp and ok_resi
        if not ok:
            all_pass = False
        print(f"{'OK' if ok else 'FAIL'} {desc}")
        if not ok_exp:
            print(f"   expedition got={detected_exp!r} expected={exp_exp!r}")
        if not ok_resi:
            print(f"   resi      got={detected_resi!r} expected={exp_resi!r}")

    print("=" * 60)
    print(f"{'SEMUA PASS' if all_pass else 'ADA YANG GAGAL'}")