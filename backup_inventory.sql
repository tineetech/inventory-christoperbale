-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Apr 2026 pada 17.12
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

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `sku`, `nama_barang`, `satuan_id`, `harga_1`, `harga_2`, `stok_minimum`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'PROD00', 'SEPATU', 1, 2000.00, 4000.00, 1, 'tesss', '2026-04-17 02:47:45', '2026-04-17 07:23:20', NULL),
(5, 'SKU-102312312', 'SENDAL', 1, 9000.00, 10000.00, 1, NULL, '2026-04-17 10:07:26', '2026-04-17 10:07:26', NULL),
(6, 'SKU-99999', 'JAKET', 1, 35000.00, 50000.00, 1, NULL, '2026-04-17 10:07:59', '2026-04-17 10:07:59', NULL);

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
(1, 'CHRISBALE', '99999999', 'CIAPUS', 'tes', '2026-04-16 09:28:40', '2026-04-16 09:32:22', NULL);

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
(20, '2026_04_14_111800_create_role_hak_akses_table', 1);

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
(3, 'admin', 'admin@gmail.com', '$2y$12$CTM7VTqVU.dIxeoeh.1DbuWaiCNQFdCLHmbIFclB6xMJnMYG/o55C', 1, NULL, NULL);

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan_detail`
--

CREATE TABLE `penjualan_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penjualan_id` bigint(20) UNSIGNED NOT NULL,
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
(1, 'super_admin', NULL, NULL);

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
('2juHQkrV2t0lHSsOlLwKxaf1G54ntk0p0LXaPmcj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV2oxblpNbTFCRTlCTFZyY0lDVk5GZXdsbFQ0aHdsaDZteWdUT2h2eSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmFuc2Frc2kvcGVtYmVsaWFuL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoxNjoicGVtYmVsaWFuLmNyZWF0ZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTU6ImxvZ2luX3BlbmdndW5hXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9', 1776445814);

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
(2, 2, 20, '2026-04-17 02:47:45', '2026-04-17 02:47:45'),
(5, 5, 90, '2026-04-17 10:07:26', '2026-04-17 10:07:26'),
(6, 6, 6, '2026-04-17 10:07:59', '2026-04-17 10:07:59');

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
(1, 2, 'masuk', 20, 0, 20, 'stok_awal', 2, 'Input stok awal saat membuat barang', 3, '2026-04-17 02:47:45', '2026-04-17 02:47:45'),
(4, 5, 'masuk', 90, 0, 90, 'stok_awal', 5, 'Input stok awal saat membuat barang', 3, '2026-04-17 10:07:26', '2026-04-17 10:07:26'),
(5, 6, 'masuk', 6, 0, 6, 'stok_awal', 6, 'Input stok awal saat membuat barang', 3, '2026-04-17 10:07:59', '2026-04-17 10:07:59');

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
  ADD KEY `stok_movement_barang_id_foreign` (`barang_id`),
  ADD KEY `stok_movement_created_by_foreign` (`created_by`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `adjust_stok_detail`
--
ALTER TABLE `adjust_stok_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `dropshipper`
--
ALTER TABLE `dropshipper`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `hak_akses`
--
ALTER TABLE `hak_akses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `role`
--
ALTER TABLE `role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `role_hak_akses`
--
ALTER TABLE `role_hak_akses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `stok_barang`
--
ALTER TABLE `stok_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `stok_movement`
--
ALTER TABLE `stok_movement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  ADD CONSTRAINT `adjust_stok_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`);

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
  ADD CONSTRAINT `pembelian_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
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
  ADD CONSTRAINT `penjualan_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
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
  ADD CONSTRAINT `stok_barang_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`);

--
-- Ketidakleluasaan untuk tabel `stok_movement`
--
ALTER TABLE `stok_movement`
  ADD CONSTRAINT `stok_movement_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  ADD CONSTRAINT `stok_movement_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
