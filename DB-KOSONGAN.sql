-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Bulan Mei 2026 pada 13.06
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `adjust_stok`
--

CREATE TABLE `adjust_stok` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_adjust` varchar(255) NOT NULL,
  `tanggal` datetime NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status` varchar(225) NOT NULL DEFAULT 'approve',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'CHRISBALE', '0', '-', NULL, '2026-04-16 09:28:40', '2026-05-01 12:58:48', NULL),
(6, 'BLESSING', '0', '-', NULL, '2026-05-01 12:56:41', '2026-05-01 12:59:08', NULL),
(7, 'CBG', '0', '-', NULL, '2026-05-01 12:57:04', '2026-05-01 12:57:04', NULL),
(8, 'CBO', '0', '-', NULL, '2026-05-01 12:57:17', '2026-05-01 12:57:17', NULL),
(9, 'JNJ', '0', '-', NULL, '2026-05-01 12:57:34', '2026-05-01 12:57:34', NULL),
(10, 'RALIN', '0', '-', NULL, '2026-05-01 12:57:50', '2026-05-01 12:57:50', NULL),
(11, 'URBAN', '0', '-', NULL, '2026-05-01 12:58:03', '2026-05-01 12:58:03', NULL),
(12, 'YEOJA', '0', '-', NULL, '2026-05-01 12:58:22', '2026-05-01 12:58:22', NULL);

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
(59, 'hapus_role', NULL, NULL),
(60, 'lihat_hitung_stok', '2026-04-24 10:14:19', '2026-04-24 10:14:19'),
(61, 'tambah_hitung_stok', '2026-04-24 10:14:33', '2026-04-24 10:16:01'),
(62, 'edit_hitung_stok', '2026-04-24 10:16:42', '2026-04-24 10:16:42'),
(63, 'hapus_hitung_stok', '2026-04-24 10:16:53', '2026-04-24 10:16:53'),
(64, 'hapus_manajemen_stok', '2026-04-24 21:31:02', '2026-04-24 21:31:02'),
(65, 'lihat_laporan_retur', '2026-04-30 03:06:50', '2026-04-30 03:06:50'),
(66, 'tambah_pembelian', '2026-05-01 11:32:44', '2026-05-01 11:32:44'),
(67, 'edit_pembelian', '2026-05-01 11:32:44', '2026-05-01 11:32:44'),
(68, 'tambah_penjualan', '2026-05-01 11:32:44', '2026-05-01 11:32:44'),
(69, 'edit_penjualan', '2026-05-01 11:32:44', '2026-05-01 11:32:44'),
(70, 'edit_laporan_retur', '2026-05-01 12:29:51', '2026-05-01 12:29:51');

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
(22, '2026_04_19_123101_update_adjust_stok_table2', 3),
(23, '2026_04_30_090601_create_retur_penjualan_table', 4);

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
(3, 'Super Admin', 'superadmin@gmail.com', '$2y$12$CTM7VTqVU.dIxeoeh.1DbuWaiCNQFdCLHmbIFclB6xMJnMYG/o55C', 1, NULL, '2026-05-01 08:10:18'),
(5, 'Admin', 'admin@gmail.com', '$2y$12$SlP8z57.iHQd0P4u.ZaENuz4NFNqaZNplJEmZMSUm0d3B5IR0lyWK', 2, '2026-04-24 21:56:29', '2026-04-24 21:56:29'),
(6, 'karyawan siti', 'karyawan@gmail.com', '$2y$12$exvRq4JwI4FalZUiSfAkA..h9MYxI2SeHlAudVWP5Vhc0YdX5Cqx6', 3, '2026-04-24 21:56:57', '2026-05-01 08:34:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_resi` varchar(255) DEFAULT NULL,
  `kode_penjualan` varchar(255) NOT NULL,
  `nomor_resi` varchar(255) DEFAULT NULL,
  `nomor_pesanan` varchar(255) DEFAULT NULL,
  `nomor_transaksi` varchar(255) DEFAULT NULL,
  `dropshipper_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `total_harga` decimal(14,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `scan_out` varchar(255) NOT NULL DEFAULT 'pending',
  `is_draft` varchar(255) NOT NULL DEFAULT 'no',
  `is_retur` varchar(255) NOT NULL DEFAULT 'no',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Struktur dari tabel `retur_penjualan`
--

CREATE TABLE `retur_penjualan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penjualan_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_retur` date NOT NULL,
  `alasan_retur` text NOT NULL,
  `status` enum('pending','diproses','selesai','ditolak') NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) DEFAULT NULL,
  `file_original_name` varchar(255) DEFAULT NULL,
  `file_mime` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `retur_penjualan_detail`
--

CREATE TABLE `retur_penjualan_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `retur_penjualan_id` bigint(20) UNSIGNED NOT NULL,
  `penjualan_detail_id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `qty_retur` int(10) UNSIGNED NOT NULL,
  `keterangan` text DEFAULT NULL,
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
(3265, 2, 1, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3266, 2, 2, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3267, 2, 6, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3268, 2, 10, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3269, 2, 14, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3270, 2, 18, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3271, 2, 21, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3272, 2, 24, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3273, 2, 27, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3274, 2, 29, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3275, 2, 31, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3276, 2, 33, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3277, 2, 44, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3278, 2, 60, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3279, 2, 65, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3280, 2, 11, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3281, 2, 66, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3282, 2, 68, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3283, 2, 25, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3284, 2, 12, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3285, 2, 67, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3286, 2, 69, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3287, 2, 26, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3288, 2, 62, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3289, 2, 70, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3290, 2, 13, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3291, 2, 20, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3292, 2, 23, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3293, 2, 63, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3294, 2, 64, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3295, 2, 19, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3296, 2, 22, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3297, 2, 55, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3298, 2, 28, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3299, 2, 30, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3300, 2, 32, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3301, 2, 34, '2026-05-01 12:38:40', '2026-05-01 12:38:40'),
(3362, 1, 1, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3363, 1, 2, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3364, 1, 6, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3365, 1, 10, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3366, 1, 14, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3367, 1, 18, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3368, 1, 21, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3369, 1, 24, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3370, 1, 27, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3371, 1, 29, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3372, 1, 31, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3373, 1, 33, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3374, 1, 44, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3375, 1, 48, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3376, 1, 52, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3377, 1, 54, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3378, 1, 56, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3379, 1, 60, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3380, 1, 65, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3381, 1, 3, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3382, 1, 7, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3383, 1, 11, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3384, 1, 15, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3385, 1, 66, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3386, 1, 68, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3387, 1, 25, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3388, 1, 45, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3389, 1, 49, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3390, 1, 57, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3391, 1, 61, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3392, 1, 4, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3393, 1, 8, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3394, 1, 12, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3395, 1, 16, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3396, 1, 67, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3397, 1, 69, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3398, 1, 26, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3399, 1, 46, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3400, 1, 50, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3401, 1, 53, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3402, 1, 58, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3403, 1, 62, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3404, 1, 70, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3405, 1, 5, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3406, 1, 9, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3407, 1, 13, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3408, 1, 17, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3409, 1, 20, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3410, 1, 23, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3411, 1, 47, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3412, 1, 51, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3413, 1, 59, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3414, 1, 63, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3415, 1, 64, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3416, 1, 19, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3417, 1, 22, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3418, 1, 55, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3419, 1, 28, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3420, 1, 30, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3421, 1, 32, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3422, 1, 34, '2026-05-01 12:39:30', '2026-05-01 12:39:30'),
(3551, 3, 1, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3552, 3, 2, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3553, 3, 6, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3554, 3, 10, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3555, 3, 14, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3556, 3, 18, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3557, 3, 21, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3558, 3, 27, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3559, 3, 29, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3560, 3, 31, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3561, 3, 33, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3562, 3, 60, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3563, 3, 65, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3564, 3, 66, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3565, 3, 68, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3566, 3, 61, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3567, 3, 46, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3568, 3, 62, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3569, 3, 19, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3570, 3, 22, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3571, 3, 55, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3572, 3, 28, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3573, 3, 30, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3574, 3, 32, '2026-05-01 12:42:06', '2026-05-01 12:42:06'),
(3575, 3, 34, '2026-05-01 12:42:06', '2026-05-01 12:42:06');

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
('69DXlfM2KXKYeSPreiLv3k8cFsu8bDgqnQyUiNKL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL21hc3Rlci9iYXJhbmciO3M6NToicm91dGUiO3M6MTI6ImJhcmFuZy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NjoiX3Rva2VuIjtzOjQwOiJnbUdURENFa0s4MlM3YkdtTVlITTRjS01Ycm9uMVNQNlp3d21CWHBuIjtzOjU1OiJsb2dpbl9wZW5nZ3VuYV81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1777640795),
('v2RlvBbTlvu3JeQkTybWlDatclqB1gGszGnQCw5r', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZENjWXRRcVp1a3E3QXRpSHFCQlltU21LcUxtc0UwWm1QMHp0NmdQdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6ImRhc2hib2FyZC5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTU6ImxvZ2luX3BlbmdndW5hXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9', 1777639372);

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_report`
--

CREATE TABLE `stok_report` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `barang_id` bigint(20) UNSIGNED NOT NULL,
  `dari_tanggal` date NOT NULL,
  `sampai_tanggal` date NOT NULL,
  `stok_saat_ini` int(11) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `input_by` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'PABRIK', '0', '-', NULL, NULL, '2026-05-01 13:05:37', NULL);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_permission` (`nama_permission`);

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
-- Indeks untuk tabel `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `retur_penjualan_penjualan_id_foreign` (`penjualan_id`),
  ADD KEY `retur_penjualan_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `retur_penjualan_detail`
--
ALTER TABLE `retur_penjualan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `retur_penjualan_detail_retur_penjualan_id_foreign` (`retur_penjualan_id`),
  ADD KEY `retur_penjualan_detail_penjualan_detail_id_foreign` (`penjualan_detail_id`),
  ADD KEY `retur_penjualan_detail_barang_id_foreign` (`barang_id`);

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
-- Indeks untuk tabel `stok_report`
--
ALTER TABLE `stok_report`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stok_report_unique_periode` (`barang_id`,`dari_tanggal`,`sampai_tanggal`),
  ADD KEY `stok_report_input_by_foreign` (`input_by`),
  ADD KEY `stok_report_confirmed_by_foreign` (`confirmed_by`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `adjust_stok_detail`
--
ALTER TABLE `adjust_stok_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT untuk tabel `dropshipper`
--
ALTER TABLE `dropshipper`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `hak_akses`
--
ALTER TABLE `hak_akses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `retur_penjualan_detail`
--
ALTER TABLE `retur_penjualan_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `role`
--
ALTER TABLE `role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `role_hak_akses`
--
ALTER TABLE `role_hak_akses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3576;

--
-- AUTO_INCREMENT untuk tabel `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `stok_barang`
--
ALTER TABLE `stok_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `stok_movement`
--
ALTER TABLE `stok_movement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT untuk tabel `stok_report`
--
ALTER TABLE `stok_report`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Ketidakleluasaan untuk tabel `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  ADD CONSTRAINT `retur_penjualan_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `retur_penjualan_penjualan_id_foreign` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `retur_penjualan_detail`
--
ALTER TABLE `retur_penjualan_detail`
  ADD CONSTRAINT `retur_penjualan_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `retur_penjualan_detail_penjualan_detail_id_foreign` FOREIGN KEY (`penjualan_detail_id`) REFERENCES `penjualan_detail` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `retur_penjualan_detail_retur_penjualan_id_foreign` FOREIGN KEY (`retur_penjualan_id`) REFERENCES `retur_penjualan` (`id`) ON DELETE CASCADE;

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

--
-- Ketidakleluasaan untuk tabel `stok_report`
--
ALTER TABLE `stok_report`
  ADD CONSTRAINT `stok_report_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stok_report_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stok_report_input_by_foreign` FOREIGN KEY (`input_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
