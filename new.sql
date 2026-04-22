-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Apr 2026 pada 16.12
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory-chrisbale`
--
mysql -u root -p `inventory-chrisbale` < "new.sql"

-- --------------------------------------------------------

--
-- Struktur dari tabel `adjust_stok`
--

CREATE TABLE `adjust_stok` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_adjust` varchar(255) NOT NULL,
  `tanggal` datetime NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `adjust_stok`
--

INSERT INTO `adjust_stok` (`id`, `kode_adjust`, `tanggal`, `keterangan`, `created_by`, `created_at`, `updated_at`) VALUES
(14, 'AS-20260419-276', '2026-04-19 00:00:00', NULL, 3, '2026-04-19 08:08:22', '2026-04-19 08:08:22'),
(15, 'AS-20260419-626', '2026-04-19 00:00:00', NULL, 3, '2026-04-19 10:05:13', '2026-04-19 10:05:13'),
(16, 'AS-20260419-760', '2026-04-19 00:00:00', NULL, 3, '2026-04-19 10:05:41', '2026-04-19 10:05:41'),
(17, 'AS-20260420-236', '2026-04-20 00:00:00', NULL, 3, '2026-04-20 08:25:22', '2026-04-20 08:25:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `adjust_stok_detail`
--

CREATE TABLE `adjust_stok_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adjust_stok_id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `qty_sistem` int(11) NOT NULL,
  `qty_fisik` int(11) NOT NULL,
  `selisih` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `adjust_stok_detail`
--

INSERT INTO `adjust_stok_detail` (`id`, `adjust_stok_id`, `barang_id`, `qty_sistem`, `qty_fisik`, `selisih`, `created_at`, `updated_at`) VALUES
(16, 14, 5, 6, 8, 2, '2026-04-19 08:08:22', '2026-04-19 08:08:22'),
(17, 15, 5, 8, 6, -2, '2026-04-19 10:05:13', '2026-04-19 10:05:13'),
(18, 16, 5, 6, 4, -2, '2026-04-19 10:05:41', '2026-04-19 10:05:41'),
(19, 17, 2, 0, 6, 6, '2026-04-20 08:25:22', '2026-04-20 08:25:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `satuan_id` bigint(20) UNSIGNED NOT NULL,
  `harga_1` decimal(12,2) DEFAULT NULL,
  `harga_2` decimal(12,2) DEFAULT NULL,
  `stok_minimum` int(11) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `sku`, `nama_barang`, `satuan_id`, `harga_1`, `harga_2`, `stok_minimum`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'SKU-BRG43', 'SEPATU', 1, 2000.00, 4000.00, 1, 'tesss 222', '2026-04-17 02:47:45', '2026-04-20 00:16:17', NULL),
(5, 'SKU-XXXXX', 'SENDAL', 1, 9000.00, 10000.00, 1, 'TES', '2026-04-17 10:07:26', '2026-04-21 08:34:15', NULL),
(6, 'SKU-99999', 'JAKET', 1, 35000.00, 50000.00, 1, 'CX', '2026-04-17 10:07:59', '2026-04-19 05:09:09', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dropshipper`
--

CREATE TABLE `dropshipper` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `dropshipper`
--

INSERT INTO `dropshipper` (`id`, `nama`, `no_telp`, `alamat`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CHRISBALE', '99999999', 'CIAPUS', 'tes', '2026-04-16 09:28:40', '2026-04-16 09:32:22', NULL),
(3, 'JJ', '41242', 'Fdsfds', NULL, '2026-04-19 07:03:23', '2026-04-19 07:03:23', NULL),
(4, 'TCBG', '321312', 'fdsfds', NULL, '2026-04-19 07:03:36', '2026-04-19 07:03:36', NULL),
(5, 'tes', '999', 'indo', NULL, '2026-04-20 18:57:43', '2026-04-20 18:57:43', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `hak_akses`
--

CREATE TABLE `hak_akses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_permission` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `hak_akses`
--

INSERT INTO `hak_akses` (`id`, `nama_permission`, `created_at`, `updated_at`) VALUES
(1, 'lihat_dashboard', '2026-04-20 17:54:44', '2026-04-20 17:54:44'),
(2, 'lihat_supplier', NULL, NULL),
(3, 'tambah_supplier', NULL, NULL),
(4, 'edit_supplier', NULL, NULL),
(5, 'hapus_supplier', NULL, NULL),
(6, 'lihat_satuan', NULL, NULL),
(7, 'tambah_satuan', NULL, NULL),
(8, 'edit_satuan', NULL, NULL),
(9, 'hapus_satuan', NULL, NULL),
(10, 'lihat_barang', NULL, NULL),
(11, 'tambah_barang', NULL, NULL),
(12, 'edit_barang', NULL, NULL),
(13, 'hapus_barang', NULL, NULL),
(14, 'lihat_dropshipper', NULL, NULL),
(15, 'tambah_dropshipper', NULL, NULL),
(16, 'edit_dropshipper', NULL, NULL),
(17, 'hapus_dropshipper', NULL, NULL),
(18, 'lihat_pembelian', NULL, NULL),
(19, 'buat_pembelian', NULL, NULL),
(20, 'hapus_pembelian', NULL, NULL),
(21, 'lihat_penjualan', NULL, NULL),
(22, 'buat_penjualan', NULL, NULL),
(23, 'hapus_penjualan', NULL, NULL),
(24, 'lihat_manajemen_stok', NULL, NULL),
(25, 'tambah_manajemen_stok', NULL, NULL),
(26, 'edit_manajemen_stok', NULL, NULL),
(27, 'lihat_laporan_pembelian', NULL, NULL),
(28, 'export_laporan_pembelian', NULL, NULL),
(29, 'lihat_laporan_penjualan', NULL, NULL),
(30, 'export_laporan_penjualan', NULL, NULL),
(31, 'lihat_laporan_stok', NULL, NULL),
(32, 'export_laporan_stok', NULL, NULL),
(33, 'lihat_laporan_barang', NULL, NULL),
(34, 'export_laporan_barang', NULL, NULL),
(35, 'lihat_pengguna', NULL, NULL),
(36, 'tambah_pengguna', NULL, NULL),
(37, 'edit_pengguna', NULL, NULL),
(38, 'hapus_pengguna', NULL, NULL),
(39, 'lihat_hak_akses', NULL, NULL),
(40, 'tambah_hak_akses', NULL, NULL),
(41, 'edit_hak_akses', NULL, NULL),
(42, 'hapus_hak_akses', NULL, NULL),
(43, 'backup_database', NULL, NULL),
(44, 'lihat_pengguna', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(45, 'tambah_pengguna', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(46, 'edit_pengguna', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(47, 'hapus_pengguna', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(48, 'lihat_hak_akses', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(49, 'tambah_hak_akses', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(50, 'edit_hak_akses', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(51, 'hapus_hak_akses', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(52, 'lihat_role_hak_akses', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(53, 'edit_role_hak_akses', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(54, 'lihat_backup_database', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(55, 'buat_backup_database', '2026-04-21 01:45:08', '2026-04-21 01:45:08'),
(56, 'lihat_role', NULL, NULL),
(57, 'tambah_role', NULL, NULL),
(58, 'edit_role', NULL, NULL),
(59, 'hapus_role', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_13_092038_create_personal_access_tokens_table', 1),
(5, '2026_04_14_111445_create_role_table', 1),
(6, '2026_04_14_111449_create_pengguna_table', 1),
(7, '2026_04_14_111450_create_hak_akses_table', 1),
(8, '2026_04_14_111451_create_satuan_table', 1),
(9, '2026_04_14_111452_create_supplier_table', 1),
(10, '2026_04_14_111454_create_dropshipper_table', 1),
(11, '2026_04_14_111455_create_barang_table', 1),
(12, '2026_04_14_111456_create_stok_barang_table', 1),
(13, '2026_04_14_111457_create_stok_movement_table', 1),
(14, '2026_04_14_111458_create_pembelian_table', 1),
(15, '2026_04_14_111500_create_pembelian_detail_table', 1),
(16, '2026_04_14_111501_create_penjualan_table', 1),
(17, '2026_04_14_111502_create_penjualan_detail_table', 1),
(18, '2026_04_14_111503_create_adjust_stok_table', 1),
(19, '2026_04_14_111504_create_adjust_stok_detail_table', 1),
(20, '2026_04_14_111800_create_role_hak_akses_table', 1),
(21, '2026_04_19_122618_update_adjust_stok_table', 2),
(22, '2026_04_19_123101_update_adjust_stok_table2', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_pembelian` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `total_harga` decimal(14,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembelian`
--

INSERT INTO `pembelian` (`id`, `kode_pembelian`, `supplier_id`, `tanggal`, `total_harga`, `keterangan`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 'PB-20260419-294', 1, '2026-04-19 00:00:00', 10000.00, NULL, 3, '2026-04-19 07:39:43', '2026-04-19 07:39:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian_detail`
--

CREATE TABLE `pembelian_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pembelian_id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pembelian_detail`
--

INSERT INTO `pembelian_detail` (`id`, `pembelian_id`, `barang_id`, `qty`, `harga`, `subtotal`, `created_at`, `updated_at`) VALUES
(41, 8, 2, 5, 2000.00, 10000.00, '2026-04-19 07:39:43', '2026-04-19 07:39:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `email`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(3, 'Super Admin', 'superadmin@gmail.com', '$2y$12$CTM7VTqVU.dIxeoeh.1DbuWaiCNQFdCLHmbIFclB6xMJnMYG/o55C', 2, NULL, '2026-04-20 19:43:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_penjualan` varchar(255) NOT NULL,
  `nomor_resi` varchar(255) DEFAULT NULL,
  `nomor_pesanan` varchar(255) DEFAULT NULL,
  `nomor_transaksi` varchar(255) DEFAULT NULL,
  `dropshipper_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `total_harga` decimal(14,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id`, `kode_penjualan`, `nomor_resi`, `nomor_pesanan`, `nomor_transaksi`, `dropshipper_id`, `tanggal`, `total_harga`, `keterangan`, `created_by`, `created_at`, `updated_at`) VALUES
(12, 'PJ-20260419-166', '', '', '', 4, '2026-04-19 00:00:00', 10000.00, NULL, 3, '2026-04-19 07:45:58', '2026-04-19 07:45:58'),
(13, 'PJ-20260419-285', 'XX', 'XX', 'XX', 3, '2026-04-19 00:00:00', 10000.00, NULL, 3, '2026-04-19 07:47:23', '2026-04-19 07:47:23'),
(14, 'PJ-20260419-324', '', '', '', 1, '2026-04-19 00:00:00', 22000.00, NULL, 3, '2026-04-19 07:48:57', '2026-04-19 07:48:57'),
(15, 'PJ-20260420-160', '', '', '', 1, '2026-04-20 00:00:00', 16000.00, NULL, 3, '2026-04-20 00:19:20', '2026-04-20 00:25:46'),
(16, 'PJ-20260420-134', 'XXCC', 'XXCC', '1', 1, '2026-04-20 00:00:00', 54000.00, NULL, 3, '2026-04-20 08:22:35', '2026-04-20 08:25:35'),
(17, 'PJ-20260420-994', 'XXCC2', '', '1', 3, '2026-04-20 00:00:00', 50000.00, NULL, 3, '2026-04-20 08:44:38', '2026-04-20 08:46:40'),
(18, 'PJ-20260420-128', '', '', '1', 4, '2026-04-20 00:00:00', 4000.00, NULL, 3, '2026-04-20 09:32:17', '2026-04-20 09:32:17'),
(19, 'PJ-20260422-172', 'fgfdgfd', 'gfdgfd', '1', 4, '2026-04-22 00:00:00', 14000.00, NULL, 3, '2026-04-22 09:01:54', '2026-04-22 09:01:54'),
(20, 'PJ-20260422-970', '', '', '1', 4, '2026-04-22 00:00:00', 10000.00, NULL, 3, '2026-04-22 09:08:38', '2026-04-22 09:08:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan_detail`
--

CREATE TABLE `penjualan_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penjualan_id` bigint(20) UNSIGNED NOT NULL,
  `nomor_resi` varchar(255) DEFAULT NULL,
  `nomor_pesanan` varchar(255) DEFAULT NULL,
  `nomor_transaksi` varchar(255) DEFAULT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penjualan_detail`
--

INSERT INTO `penjualan_detail` (`id`, `penjualan_id`, `nomor_resi`, `nomor_pesanan`, `nomor_transaksi`, `barang_id`, `qty`, `harga`, `subtotal`, `created_at`, `updated_at`) VALUES
(37, 12, '', '', '', 2, 5, 2000.00, 10000.00, '2026-04-19 07:45:58', '2026-04-19 07:45:58'),
(38, 13, 'XX', 'XX', 'XX', 2, 5, 2000.00, 10000.00, '2026-04-19 07:47:23', '2026-04-19 07:47:23'),
(39, 14, '', '', '', 2, 11, 2000.00, 22000.00, '2026-04-19 07:48:57', '2026-04-19 07:48:57'),
(40, 15, '', '', '', 2, 4, 4000.00, 16000.00, '2026-04-20 00:19:20', '2026-04-20 00:25:46'),
(42, 16, 'XXCC', 'XXCC', '1', 6, 1, 50000.00, 50000.00, '2026-04-20 08:22:35', '2026-04-20 08:25:35'),
(43, 16, '', '', '2', 2, 1, 4000.00, 4000.00, '2026-04-20 08:25:35', '2026-04-20 08:25:35'),
(44, 17, 'XXCC2', '', '1', 6, 1, 50000.00, 50000.00, '2026-04-20 08:44:38', '2026-04-20 08:46:40'),
(45, 18, '', '', '1', 2, 1, 4000.00, 4000.00, '2026-04-20 09:32:17', '2026-04-20 09:32:17'),
(46, 19, 'fgfdgfd', 'gfdgfd', '1', 2, 1, 4000.00, 4000.00, '2026-04-22 09:01:54', '2026-04-22 09:01:54'),
(47, 19, 'fgfdgfd', 'gfdgfd', '2', 5, 1, 10000.00, 10000.00, '2026-04-22 09:01:54', '2026-04-22 09:01:54'),
(48, 20, '', '', '1', 5, 1, 10000.00, 10000.00, '2026-04-22 09:08:38', '2026-04-22 09:08:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `role`
--

CREATE TABLE `role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_role` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role`
--

INSERT INTO `role` (`id`, `nama_role`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', NULL, NULL),
(2, 'admin', NULL, NULL),
(3, 'karyawan', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_hak_akses`
--

CREATE TABLE `role_hak_akses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `hak_akses_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_hak_akses`
--

INSERT INTO `role_hak_akses` (`id`, `role_id`, `hak_akses_id`, `created_at`, `updated_at`) VALUES
(961, 1, 1, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(962, 1, 2, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(963, 1, 6, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(964, 1, 10, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(965, 1, 14, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(966, 1, 18, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(967, 1, 21, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(968, 1, 24, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(969, 1, 27, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(970, 1, 29, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(971, 1, 31, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(972, 1, 33, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(973, 1, 35, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(974, 1, 39, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(975, 1, 44, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(976, 1, 48, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(977, 1, 52, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(978, 1, 54, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(979, 1, 56, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(980, 1, 3, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(981, 1, 7, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(982, 1, 11, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(983, 1, 15, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(984, 1, 25, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(985, 1, 36, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(986, 1, 40, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(987, 1, 45, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(988, 1, 49, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(989, 1, 57, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(990, 1, 4, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(991, 1, 8, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(992, 1, 12, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(993, 1, 16, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(994, 1, 26, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(995, 1, 37, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(996, 1, 41, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(997, 1, 46, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(998, 1, 50, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(999, 1, 53, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1000, 1, 58, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1001, 1, 5, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1002, 1, 9, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1003, 1, 13, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1004, 1, 17, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1005, 1, 20, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1006, 1, 23, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1007, 1, 38, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1008, 1, 42, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1009, 1, 47, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1010, 1, 51, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1011, 1, 59, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1012, 1, 19, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1013, 1, 22, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1014, 1, 55, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1015, 1, 28, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1016, 1, 30, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1017, 1, 32, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1018, 1, 34, '2026-04-20 20:07:56', '2026-04-20 20:07:56'),
(1019, 2, 1, '2026-04-21 08:32:36', '2026-04-21 08:32:36'),
(1020, 2, 2, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1021, 2, 6, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1022, 2, 10, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1023, 2, 14, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1024, 2, 18, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1025, 2, 21, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1026, 2, 24, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1027, 2, 27, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1028, 2, 29, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1029, 2, 31, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1030, 2, 33, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1031, 2, 35, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1032, 2, 39, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1033, 2, 44, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1034, 2, 48, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1035, 2, 52, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1036, 2, 54, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1037, 2, 56, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1038, 2, 3, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1039, 2, 7, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1040, 2, 11, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1041, 2, 15, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1042, 2, 25, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1043, 2, 36, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1044, 2, 40, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1045, 2, 45, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1046, 2, 49, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1047, 2, 57, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1048, 2, 4, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1049, 2, 8, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1050, 2, 12, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1051, 2, 16, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1052, 2, 26, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1053, 2, 37, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1054, 2, 41, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1055, 2, 46, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1056, 2, 50, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1057, 2, 53, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1058, 2, 58, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1059, 2, 5, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1060, 2, 9, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1061, 2, 13, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1062, 2, 17, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1063, 2, 20, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1064, 2, 23, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1065, 2, 38, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1066, 2, 42, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1067, 2, 47, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1068, 2, 51, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1069, 2, 59, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1070, 2, 19, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1071, 2, 22, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1072, 2, 55, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1073, 2, 28, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1074, 2, 30, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1075, 2, 32, '2026-04-21 08:32:37', '2026-04-21 08:32:37'),
(1076, 2, 34, '2026-04-21 08:32:37', '2026-04-21 08:32:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `satuan`
--

CREATE TABLE `satuan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_satuan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `satuan`
--

INSERT INTO `satuan` (`id`, `nama_satuan`, `created_at`, `updated_at`) VALUES
(1, 'PCS', '2026-04-15 10:51:05', '2026-04-15 10:51:05'),
(2, 'BAL', '2026-04-15 10:51:37', '2026-04-15 10:51:37'),
(3, 'tes 00', '2026-04-15 10:51:44', '2026-04-15 10:52:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2GX35YXJUmDW8T1fgWmGV9bgttVVnKOcz7RFhWrn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicVUzb0lmeEFkWWdWTktoellVRllkSXlpbDRsQ3dPVXgyNk9WT21rUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmFuc2Frc2kvcGVuanVhbGFuIjtzOjU6InJvdXRlIjtzOjE1OiJwZW5qdWFsYW4uaW5kZXgiO31zOjU1OiJsb2dpbl9wZW5nZ3VuYV81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1776874261),
('8msfxlwd3knlfV5pcFTsvZDA9KYwHuV5XwOM5TO0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWkNoS1M5ejBFc1RYbDVuelVLazBTWU5xMktYYVhYcHVHcEFteW04VSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMDoibG9naW4udmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1776869916),
('xeP7xhvsDSRvPCzHLt1KZosLS3dRdNuuKcFdneKs', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM29OQWtnT2JsQVhMSDFqcXhzaDhNOW5iaUd2NHVFNURWQTlrZUZtOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMDoibG9naW4udmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1776869915);

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_barang`
--

CREATE TABLE `stok_barang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah_stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stok_barang`
--

INSERT INTO `stok_barang` (`id`, `barang_id`, `jumlah_stok`, `created_at`, `updated_at`) VALUES
(2, 2, 3, '2026-04-17 02:47:45', '2026-04-22 09:01:54'),
(5, 5, 2, '2026-04-17 10:07:26', '2026-04-22 09:08:38'),
(6, 6, 8, '2026-04-17 10:07:59', '2026-04-20 08:44:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_movement`
--

CREATE TABLE `stok_movement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `jenis` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `stok_sebelum` int(11) NOT NULL,
  `stok_sesudah` int(11) NOT NULL,
  `referensi_tipe` varchar(255) DEFAULT NULL,
  `referensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stok_movement`
--

INSERT INTO `stok_movement` (`id`, `barang_id`, `jenis`, `qty`, `stok_sebelum`, `stok_sesudah`, `referensi_tipe`, `referensi_id`, `keterangan`, `created_by`, `created_at`, `updated_at`) VALUES
(190, 2, 'masuk', 5, 20, 25, 'pembelian', 8, 'Pembelian PB-20260419-294', 3, '2026-04-19 07:39:43', '2026-04-19 07:39:43'),
(191, 2, 'keluar', 5, 25, 20, 'penjualan', 12, 'Penjualan PJ-20260419-166', 3, '2026-04-19 07:45:58', '2026-04-19 07:45:58'),
(192, 2, 'keluar', 5, 20, 15, 'penjualan', 13, 'Penjualan PJ-20260419-285', 3, '2026-04-19 07:47:23', '2026-04-19 07:47:23'),
(193, 2, 'keluar', 11, 15, 4, 'penjualan', 14, 'Penjualan PJ-20260419-324', 3, '2026-04-19 07:48:57', '2026-04-19 07:48:57'),
(194, 5, 'adjustment', 2, 6, 8, 'adjust_stok', 14, 'Adjustment stok', 3, '2026-04-19 08:08:22', '2026-04-19 08:08:22'),
(195, 5, 'adjustment', -2, 8, 6, 'adjust_stok', 15, 'Adjustment stok', 3, '2026-04-19 10:05:13', '2026-04-19 10:05:13'),
(196, 5, 'adjustment', -2, 6, 4, 'adjust_stok', 16, 'Adjustment stok', 3, '2026-04-19 10:05:41', '2026-04-19 10:05:41'),
(197, 2, 'keluar', 2, 4, 2, 'penjualan', 15, 'Penjualan PJ-20260420-160', 3, '2026-04-20 00:19:20', '2026-04-20 00:19:20'),
(198, 2, 'keluar', 2, 2, 0, 'penjualan_update', 15, 'Edit penjualan PJ-20260420-160', 3, '2026-04-20 00:25:46', '2026-04-20 00:25:46'),
(199, 5, 'keluar', 1, 4, 3, 'penjualan', 16, 'Penjualan PJ-20260420-134', 3, '2026-04-20 08:22:35', '2026-04-20 08:22:35'),
(200, 6, 'keluar', 1, 10, 9, 'penjualan', 16, 'Penjualan PJ-20260420-134', 3, '2026-04-20 08:22:35', '2026-04-20 08:22:35'),
(201, 2, 'adjustment', 6, 0, 6, 'adjust_stok', 17, 'Adjustment stok', 3, '2026-04-20 08:25:22', '2026-04-20 08:25:22'),
(202, 2, 'keluar', 1, 6, 5, 'penjualan_update', 16, 'Edit penjualan PJ-20260420-134', 3, '2026-04-20 08:25:35', '2026-04-20 08:25:35'),
(203, 5, 'masuk', 1, 3, 4, 'penjualan_update_delete_item', 16, 'Hapus item saat edit PJ-20260420-134', 3, '2026-04-20 08:25:35', '2026-04-20 08:25:35'),
(204, 6, 'keluar', 1, 9, 8, 'penjualan', 17, 'Penjualan PJ-20260420-994', 3, '2026-04-20 08:44:38', '2026-04-20 08:44:38'),
(205, 2, 'keluar', 1, 5, 4, 'penjualan', 18, 'Penjualan PJ-20260420-128', 3, '2026-04-20 09:32:17', '2026-04-20 09:32:17'),
(206, 2, 'keluar', 1, 4, 3, 'penjualan', 19, 'Penjualan PJ-20260422-172', 3, '2026-04-22 09:01:54', '2026-04-22 09:01:54'),
(207, 5, 'keluar', 1, 4, 3, 'penjualan', 19, 'Penjualan PJ-20260422-172', 3, '2026-04-22 09:01:54', '2026-04-22 09:01:54'),
(208, 5, 'keluar', 1, 3, 2, 'penjualan', 20, 'Penjualan PJ-20260422-970', 3, '2026-04-22 09:08:38', '2026-04-22 09:08:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_supplier` varchar(255) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id`, `nama_supplier`, `no_telp`, `alamat`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PABRIK', '08999999999', 'JL CIAPUS BARU', NULL, NULL, '2026-04-16 03:33:35', NULL),
(6, 'PABRIK B', NULL, NULL, NULL, '2026-04-15 10:23:32', '2026-04-15 10:23:32', NULL),
(7, 'PABRIK C', NULL, NULL, NULL, '2026-04-15 10:23:58', '2026-04-15 10:23:58', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `adjust_stok`
--
ALTER TABLE `adjust_stok`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adjust_stok_kode_adjust_unique` (`kode_adjust`),
  ADD KEY `adjust_stok_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `adjust_stok_detail`
--
ALTER TABLE `adjust_stok_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adjust_stok_detail_adjust_stok_id_foreign` (`adjust_stok_id`),
  ADD KEY `adjust_stok_detail_barang_id_foreign` (`barang_id`);

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barang_sku_unique` (`sku`),
  ADD KEY `barang_satuan_id_foreign` (`satuan_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `dropshipper`
--
ALTER TABLE `dropshipper`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `hak_akses`
--
ALTER TABLE `hak_akses`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pembelian_kode_pembelian_unique` (`kode_pembelian`),
  ADD KEY `pembelian_supplier_id_foreign` (`supplier_id`),
  ADD KEY `pembelian_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembelian_detail_pembelian_id_foreign` (`pembelian_id`),
  ADD KEY `pembelian_detail_barang_id_foreign` (`barang_id`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengguna_email_unique` (`email`),
  ADD KEY `pengguna_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penjualan_kode_penjualan_unique` (`kode_penjualan`),
  ADD KEY `penjualan_dropshipper_id_foreign` (`dropshipper_id`),
  ADD KEY `penjualan_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjualan_detail_penjualan_id_foreign` (`penjualan_id`),
  ADD KEY `penjualan_detail_barang_id_foreign` (`barang_id`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indeks untuk tabel `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `role_hak_akses`
--
ALTER TABLE `role_hak_akses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_hak_akses_role_id_foreign` (`role_id`),
  ADD KEY `role_hak_akses_hak_akses_id_foreign` (`hak_akses_id`);

--
-- Indeks untuk tabel `satuan`
--
ALTER TABLE `satuan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `stok_barang`
--
ALTER TABLE `stok_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stok_barang_barang_id_foreign` (`barang_id`);

--
-- Indeks untuk tabel `stok_movement`
--
ALTER TABLE `stok_movement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stok_movement_created_by_foreign` (`created_by`),
  ADD KEY `stok_movement_barang_id_foreign` (`barang_id`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `adjust_stok`
--
ALTER TABLE `adjust_stok`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `adjust_stok_detail`
--
ALTER TABLE `adjust_stok_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `dropshipper`
--
ALTER TABLE `dropshipper`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `hak_akses`
--
ALTER TABLE `hak_akses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `role`
--
ALTER TABLE `role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `role_hak_akses`
--
ALTER TABLE `role_hak_akses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1077;

--
-- AUTO_INCREMENT untuk tabel `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `stok_barang`
--
ALTER TABLE `stok_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `stok_movement`
--
ALTER TABLE `stok_movement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `adjust_stok`
--
ALTER TABLE `adjust_stok`
  ADD CONSTRAINT `adjust_stok_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `adjust_stok_detail`
--
ALTER TABLE `adjust_stok_detail`
  ADD CONSTRAINT `adjust_stok_detail_adjust_stok_id_foreign` FOREIGN KEY (`adjust_stok_id`) REFERENCES `adjust_stok` (`id`),
  ADD CONSTRAINT `adjust_stok_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `pembelian_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  ADD CONSTRAINT `pembelian_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembelian_detail_pembelian_id_foreign` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelian` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `pengguna_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `penjualan_dropshipper_id_foreign` FOREIGN KEY (`dropshipper_id`) REFERENCES `dropshipper` (`id`);

--
-- Ketidakleluasaan untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD CONSTRAINT `penjualan_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penjualan_detail_penjualan_id_foreign` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`);

--
-- Ketidakleluasaan untuk tabel `role_hak_akses`
--
ALTER TABLE `role_hak_akses`
  ADD CONSTRAINT `role_hak_akses_hak_akses_id_foreign` FOREIGN KEY (`hak_akses_id`) REFERENCES `hak_akses` (`id`),
  ADD CONSTRAINT `role_hak_akses_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Ketidakleluasaan untuk tabel `stok_barang`
--
ALTER TABLE `stok_barang`
  ADD CONSTRAINT `stok_barang_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stok_movement`
--
ALTER TABLE `stok_movement`
  ADD CONSTRAINT `stok_movement_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stok_movement_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
