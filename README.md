# Christoperbale POS & Inventory

Sistem POS (Point of Sale) dan inventori berbasis web untuk mengelola produk, stok, pembelian, penjualan, pembayaran, retur, laporan, serta impor data penjualan dari resi marketplace.

Aplikasi utama dibangun dengan **Laravel 12** dan **Blade + Tailwind CSS**, dilengkapi layanan **FastAPI + Celery** di folder `backend-ocr` untuk membaca resi/Nota melalui OCR.

---

## Fitur Utama

### Autentikasi dan Hak Akses
- Login menggunakan email atau nama pengguna dengan guard `pengguna`.
- Lupa dan reset password melalui email.
- Manajemen pengguna, role, hak akses, dan pemetaan role-hak akses (RBAC).
- Middleware permission per aksi dan modul, misalnya `permission:lihat,penjualan`.
- API authentication menggunakan Laravel Sanctum.

### Master Data
- Satuan, supplier, dropshipper, dan brand.
- Barang beserta harga, HPP, stok minimum, barcode, dan riwayat HPP.
- Produk, varian produk, dan foto produk.
- Diskon, voucher, dan voucher pengguna.
- Chatbot FAQ dan banner.
- Pengaturan website.

### Manajemen Stok
- Stok barang dan stok movement.
- Penyesuaian stok (adjust stok).
- Hitung stok / stock opname.
- Analisa stok dan peringatan stok kritis.
- Import barang dari file Excel/CSV.

### Pembelian
- Transaksi pembelian beserta detail barang.
- Update harga beli dan HPP.
- Hapus transaksi secara massal.

### Penjualan
- Penjualan satuan maupun multiple.
- Draft penjualan dan proses release draft.
- Penjualan web/e-commerce.
- Pembayaran dan status pembayaran.
- Cetak dan unduh struk, termasuk cetak massal.
- Barcode produk.
- Retur penjualan dan perubahan status retur.
- Scan-out berdasarkan nomor resi.

### Import Penjualan dan OCR
- Import resi Shopee, TikTok, dan Tokopedia/JNT.
- Import multiple resi secara sinkron maupun asinkron.
- Polling status proses OCR melalui API.
- Dukungan pembacaan file gambar dan PDF melalui layanan `backend-ocr`.

### Laporan
Tersedia dalam tampilan web, cetak, PDF, dan Excel:
- Laporan pembelian.
- Laporan penjualan.
- Laporan penjualan web.
- Laporan pembayaran.
- Laporan stok, input stok, dan ringkasan stok.
- Laporan barang.
- Laporan stok kritis.

### Operasional
- Dashboard ringkasan.
- Notifikasi.
- Backup database dan reset database.
- Export/import Excel dan PDF.

---

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend utama | PHP 8.2+, Laravel 12 |
| Database | MySQL / MariaDB |
| Tampilan | Blade, Tailwind CSS 4, Vite 7 |
| Autentikasi API | Laravel Sanctum |
| Excel/CSV | `maatwebsite/excel` |
| PDF | `barryvdh/laravel-dompdf`, `tecnickcom/tcpdf` |
| Barcode | `milon/barcode` |
| OCR service | Python, FastAPI, Celery, Redis, Tesseract, OpenCV |

---

## Kebutuhan Sistem

- PHP >= 8.2 beserta ekstensi umum Laravel.
- Composer 2.
- Node.js dan npm.
- MySQL/MariaDB.
- Python 3.10+ untuk layanan OCR.
- Redis untuk antrean Celery.
- Tesseract OCR dan Poppler (untuk pembacaan PDF) pada mesin yang menjalankan `backend-ocr`.

---

## Instalasi

### 1. Siapkan proyek

```bash
composer install
```

Salin konfigurasi environment:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

> Windows PowerShell tidak mendukung redirect `<`. Gunakan `cmd /c` atau import lewat phpMyAdmin jika ingin menjalankan perintah SQL dari terminal.

### 2. Siapkan database

Buat database MySQL:

```sql
CREATE DATABASE inventory-chrisbale
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Tersedia dua dump database di folder `database/`:

- `DB_DEMO.sql` — skema beserta data demo.
- `DB-KOSONGAN.sql` — database kosong/bersih.

Import melalui phpMyAdmin, atau lewat terminal:

```bash
# Linux/macOS
mysql -u root -p inventory-chrisbale < database/DB_DEMO.sql

# Windows (cmd)
cmd /c "mysql -u root -p inventory-chrisbale < database\DB_DEMO.sql"
```

Alternatif tanpa dump: jalankan migrasi.

```bash
php artisan migrate
```

> Akun pengguna awal mengikuti data pada dump yang diimpor. `DatabaseSeeder` bawaan hanya membuat user percobaan Laravel, bukan akun admin aplikasi.

### 3. Konfigurasi `.env`

Sesuaikan setidaknya nilai berikut:

```dotenv
APP_NAME=Christoperbale
APP_ENV=local
APP_URL=http://localhost:8002

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory-chrisbale
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Layanan OCR
FASTAPI_URL=http://127.0.0.1:9000
# URL frontend untuk tautan reset password
FE_URL=http://localhost:8000

# Diperlukan untuk fitur lupa password
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Siapkan storage dan aset

```bash
php artisan storage:link
npm install
npm run build
```

### 5. Jalankan aplikasi

```bash
php artisan serve --host=0.0.0.0 --port=8002
```

Untuk mode pengembangan beserta Vite:

```bash
npm run dev
```

Atau jalankan seluruh proses pengembangan Laravel sekaligus:

```bash
composer dev
```

Buka `http://localhost:8002`.

---

## Menjalankan Layanan OCR (`backend-ocr`)

Layanan ini dipakai untuk membaca resi/Nota marketplace. Pastikan Redis aktif.

```powershell
cd backend-ocr
python -m venv venv
.\venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

Buat `backend-ocr/.env` jika ingin mengganti alamat Redis:

```dotenv
REDIS_URL=redis://localhost:6379/0
```

Jalankan API FastAPI:

```bash
uvicorn main:app --reload --host=0.0.0.0 --port=9000
```

Jalankan Celery worker di terminal terpisah:

```bash
celery -A celery_app worker --loglevel=info --queues=ocr --concurrency=4
```

Jika terjadi masalah worker di Windows, coba:

```bash
celery -A celery_app worker --loglevel=info --queues=ocr --pool=solo
```

Endpoint OCR utama:

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/scan-resi` | OCR satu file resi |
| POST | `/scan-resi-multiple` | OCR multiple resi (sync) |
| POST | `/scan-resi-multiple-async` | Membuat job OCR async |
| GET | `/job-status/{job_id}` | Mengecek status job OCR |
| POST | `/convert-pdf` | Konversi/olah PDF |

Pastikan `FASTAPI_URL` pada `.env` Laravel mengarah ke layanan ini.

---

## Struktur Proyek

```text
app/
├── Exports/                 # Export laporan Excel
├── Helpers/                 # Helper permission
├── Http/
│   ├── Controllers/         # Controller web dan API
│   └── Middleware/          # Auth pengguna dan cek hak akses
├── Imports/                 # Import barang Excel
├── Models/                  # Model Eloquent
├── Notifications/           # Notifikasi reset password
└── Providers/

database/
├── backups/                 # Backup database
├── migrations/              # Migrasi skema
├── seeders/                 # Seeder
├── DB_DEMO.sql              # Dump dengan data demo
└── DB-KOSONGAN.sql          # Dump database kosong

resources/views/pages/       # Halaman Blade per modul
routes/
├── web.php                  # Route halaman web
├── api.php                  # Route API produk, import, scan-out
└── console.php

backend-ocr/                 # FastAPI + Celery + parser marketplace
```

---

## Ringkasan Route Penting

| Area | Prefix/Route |
|---|---|
| Login | `/login` |
| Dashboard | `/dashboard` |
| Master data | `/master/*` |
| Stok | `/stok-barang`, `/stok-movement`, `/transaksi/manage-stok`, `/transaksi/hitung-stok` |
| Pembelian | `/transaksi/pembelian` |
| Penjualan | `/transaksi/penjualan` |
| Penjualan web | `/transaksi/penjualan/web` |
| Pembayaran | `/transaksi/pembayaran` |
| Retur | `/laporan/penjualan/list-retur` |
| Laporan | `/laporan/*` |
| Pengaturan | `/pengaturan-web` |
| Backup | `/backup-database` |

API:

| Method | Endpoint |
|---|---|
| GET | `/api/product/search` |
| GET | `/api/product/search-by-word` |
| GET | `/api/product/search-grouped` |
| POST | `/api/barang/import` |
| POST | `/api/penjualan/import/multiple-resi` |
| POST | `/api/import-multiple-resi-async` |
| GET | `/api/import-job-status/{jobId}` |
| GET | `/api/penjualan/list` |
| POST | `/api/penjualan/scan-out` |
| GET | `/api/penjualan/by-resi/{nomorResi}` |

---

## Hak Akses

Aplikasi menggunakan RBAC dengan alur:

```text
Pengguna -> Role -> RoleHakAkses -> HakAkses
```

Pengecekan akses dilakukan melalui middleware `permission:aksi,modul` dan helper `hasPermission($action, $module)`. Contoh aksi: `lihat`, `tambah`, `edit`, `buat`, `hapus`.

Jika muncul halaman **403 Tidak memiliki akses**, periksa pemetaan hak akses role pengguna melalui menu **Role Hak Akses**.

---

## Pengujian dan Format Kode

```bash
php artisan test
./vendor/bin/pint
```

---

## Backup Database

Backup dapat dilakukan dari menu aplikasi (`/backup-database`). Tersedia juga script Composer:

```bash
composer backup-db
```

Script bawaan menggunakan path `mysqldump` milik XAMPP. Sesuaikan path tersebut dengan lingkungan Anda sebelum digunakan.

---

## Catatan Pengembangan

- Folder `vendor`, `node_modules`, `public/build`, `.env`, dan environment virtual Python tidak disertakan dalam repository.
- Jangan menyimpan kredensial SMTP, database, atau layanan lain di repository.
- Jalankan `php artisan optimize:clear` setelah mengubah konfigurasi atau environment.
- Untuk import multiple resi, pastikan layanan FastAPI, Redis, dan Celery worker aktif.
- Perintah `composer dev` menjalankan server Laravel, queue listener, log viewer, dan Vite secara bersamaan.

---

## Lisensi

Proyek ini merupakan aplikasi internal Christoperbale. Framework Laravel yang digunakan berlisensi MIT.
