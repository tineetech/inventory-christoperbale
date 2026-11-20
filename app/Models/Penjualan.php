<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    public const LAPORAN_EKSPEDISI_GROUPS = [
        'shopee' => 'Shopee',
        'tiktok' => 'TikTok',
        'lazada' => 'Lazada',
        'unknown' => 'Resi Unknown',
    ];

    protected $casts = [
        'is_harga_keuntungan_manual' => 'boolean',
    ];

    protected $fillable = [
        'file_resi',
        'kode_penjualan',
        'nomor_resi',
        'nomor_pesanan',
        'nomor_transaksi',
        'dropshipper_id',
        'tanggal',
        'total_harga',
        'harga_discount',
        'shipping_cost',
        'service_fee',
        'subtotal_harga',
        'harga_cair',
        'harga_keuntungan',
        'is_harga_keuntungan_manual',
        'keterangan',
        'status',
        'order_web',
        'scan_out',
        'is_draft',
        'is_retur',
        'strukprint_status',
        'created_by'
    ];

    public static function getLaporanMarketplaceGroup(?string $nomorPesanan): ?string
    {
        $order = trim((string) $nomorPesanan);

        if ($order === '') {
            return null;
        }

        if (strlen($order) === 18 && ctype_digit($order)) {
            return 'tiktok';
        }

        if (strlen($order) === 16 && ctype_digit($order)) {
            return 'lazada';
        }

        if (strlen($order) === 14 && preg_match('/^\d{6}[A-Z0-9]{8}$/i', $order)) {
            return 'shopee';
        }

        if (strlen($order) === 19 && preg_match('/^\d{6}[A-Z0-9]{13}$/i', $order)) {
            return 'shopee';
        }

        return null;
    }

    public static function resiRemoteBaseUrl(): ?string
    {
        $base = trim((string) config('filesystems.resi_remote_url'));

        return $base === '' ? null : rtrim($base, '/');
    }

    public static function resiRemoteUrl(?string $fileResi): ?string
    {
        $fileResi = self::normalizeResiPath($fileResi);
        $base = self::resiRemoteBaseUrl();

        if ($fileResi === null || $base === null) {
            return null;
        }

        return $base . '/' . $fileResi;
    }

    public static function resiLocalPath(?string $fileResi): ?string
    {
        $fileResi = self::normalizeResiPath($fileResi);

        if ($fileResi === null) {
            return null;
        }

        return storage_path('app/public/' . $fileResi);
    }

    public static function resiDisplayUrl(?string $fileResi): ?string
    {
        $fileResi = self::normalizeResiPath($fileResi);

        if ($fileResi === null) {
            return null;
        }

        $localPath = self::resiLocalPath($fileResi);

        if ($localPath && is_file($localPath)) {
            return asset('storage/' . $fileResi);
        }

        return self::resiRemoteUrl($fileResi) ?? asset('storage/' . $fileResi);
    }

    private static function normalizeResiPath(?string $fileResi): ?string
    {
        $path = ltrim(str_replace('\\', '/', trim((string) $fileResi)), '/');

        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }

    public function dropshipper()
    {
        return $this->belongsTo(Dropshipper::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'penjualan_id');
    }

    public function address()
    {
        return $this->hasOne(PenjualanAddress::class, 'penjualan_id');
    }

    public function shipment()
    {
        return $this->hasOne(PenjualanShipment::class, 'penjualan_id');
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class, 'order_id');
    }

    /**
     * Total harga sesuai mode laporan (visualisasi saja, tidak mengubah data).
     */
    public function totalHargaByMode(string $mode = 'harga_reseller'): float
    {
        if (!$this->relationLoaded('detail')) {
            $this->load('detail.barang');
        }

        if ($this->detail->isEmpty()) {
            return (float) ($this->total_harga ?? 0);
        }

        return (float) $this->detail->sum(fn($detail) => $detail->subtotalByMode($mode));
    }

    public static function applyLaporanPenjualanFilters($query, array $filters, bool $applyReturFilter = true, bool $ignoreDatesOnSearch = false)
    {
        if ($applyReturFilter) {
            // Default: penjualan normal + retur.
            // show_retur = no → hanya penjualan normal.
            // only_retur = yes → hanya penjualan retur.
            if (($filters['only_retur'] ?? 'no') === 'yes') {
                $query->where('penjualan.is_retur', 'yes');
            } elseif (($filters['show_retur'] ?? 'yes') !== 'yes') {
                $query->where('penjualan.is_retur', 'no');
            }
        }

        $search = trim((string) ($filters['search'] ?? ''));
        $applyDateRange = !($ignoreDatesOnSearch && $search !== '');

        if ($applyDateRange) {
            if (!empty($filters['dari_tanggal'])) {
                $dari = $filters['dari_tanggal'] . ' ' . (($filters['dari_jam'] ?? null) ?: '00:00') . ':00';
                $query->where('penjualan.tanggal', '>=', $dari);
            }
            if (!empty($filters['sampai_tanggal'])) {
                $sampai = $filters['sampai_tanggal'] . ' ' . (($filters['sampai_jam'] ?? null) ?: '23:59') . ':59';
                $query->where('penjualan.tanggal', '<=', $sampai);
            }
        }

        if (!empty($filters['dropshipper_id'])) {
            $query->where('penjualan.dropshipper_id', $filters['dropshipper_id']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('penjualan.kode_penjualan', 'like', "%{$search}%")
                  ->orWhere('penjualan.nomor_resi', 'like', "%{$search}%")
                  ->orWhere('penjualan.nomor_pesanan', 'like', "%{$search}%")
                  ->orWhere('penjualan.keterangan', 'like', "%{$search}%");
            });
        }

        // Filter pengelompokan nomor resi berdasarkan prefix.
        if (!empty($filters['ekspedisi'])) {
            $resi = "UPPER(TRIM(penjualan.nomor_resi))";

            if ($filters['ekspedisi'] === 'spx') {
                $query->whereRaw("{$resi} LIKE 'SPX%'");
            } elseif ($filters['ekspedisi'] === 'jnt') {
                $query->where(function ($q) use ($resi) {
                    $q->whereRaw("{$resi} LIKE 'JX%'")
                      ->orWhereRaw("{$resi} LIKE 'JY%'")
                      ->orWhereRaw("{$resi} LIKE 'JZ%'");
                });
            } elseif ($filters['ekspedisi'] === 'anteraja') {
                $query->whereRaw("{$resi} REGEXP '^[0-9]'");
            } elseif ($filters['ekspedisi'] === 'lazada') {
                $query->whereRaw("{$resi} LIKE 'LX%'");
            } elseif ($filters['ekspedisi'] === 'lainnya') {
                $query->where(function ($q) use ($resi) {
                    $q->whereNull('penjualan.nomor_resi')
                      ->orWhereRaw("TRIM(penjualan.nomor_resi) = ''")
                      ->orWhere(function ($sub) use ($resi) {
                          $sub->whereRaw("TRIM(penjualan.nomor_resi) <> ''")
                              ->whereRaw("{$resi} NOT LIKE 'SPX%'")
                              ->whereRaw("{$resi} NOT LIKE 'JX%'")
                              ->whereRaw("{$resi} NOT LIKE 'JY%'")
                              ->whereRaw("{$resi} NOT LIKE 'JZ%'")
                              ->whereRaw("{$resi} NOT LIKE 'LX%'")
                              ->whereRaw("{$resi} NOT REGEXP '^[0-9]'");
                      });
                });
            }
        }

        return $query;
    }

    public static function applyPenjualanWebFilters($query, array $filters)
    {
        $dateFrom = $filters['date_from'] ?? $filters['dari_tanggal'] ?? null;
        $dateTo = $filters['date_to'] ?? $filters['sampai_tanggal'] ?? null;
        $timeFrom = ($filters['time_from'] ?? $filters['dari_jam'] ?? null) ?: '00:00';
        $timeTo = ($filters['time_to'] ?? $filters['sampai_jam'] ?? null) ?: '23:59';

        if (!empty($dateFrom)) {
            $query->where('penjualan.tanggal', '>=', $dateFrom . ' ' . $timeFrom . ':00');
        }
        if (!empty($dateTo)) {
            $query->where('penjualan.tanggal', '<=', $dateTo . ' ' . $timeTo . ':59');
        }

        if (!empty($filters['status'])) {
            $query->where('penjualan.status', $filters['status']);
        }

        if (!empty($filters['scan_out'])) {
            $query->where('penjualan.scan_out', $filters['scan_out']);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('penjualan.kode_penjualan', 'like', "%{$search}%")
                  ->orWhere('penjualan.nomor_resi', 'like', "%{$search}%")
                  ->orWhere('penjualan.nomor_pesanan', 'like', "%{$search}%")
                  ->orWhere('penjualan.nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('penjualan.keterangan', 'like', "%{$search}%")
                  ->orWhereHas('address', fn($q2) => $q2->where('recipient_name', 'like', "%{$search}%"))
                  ->orWhereHas('dropshipper', fn($q2) => $q2->where('nama', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    public static function targetHargaKeuntunganQuery(array $filters, bool $onlyWeb = false)
    {
        $query = static::query()->select('penjualan.id');

        if ($onlyWeb) {
            $query->where('penjualan.order_web', 1);
            static::applyPenjualanWebFilters($query, $filters);
        } else {
            static::applyLaporanPenjualanFilters($query, $filters, true, true);
        }

        return $query;
    }

    public static function countManualHargaKeuntungan(array $filters, bool $onlyWeb = false): int
    {
        return static::targetHargaKeuntunganQuery($filters, $onlyWeb)
            ->where('penjualan.is_harga_keuntungan_manual', true)
            ->count();
    }

    public static function generateHargaKeuntungan(array $filters, bool $onlyWeb = false): int
    {
        $target = static::targetHargaKeuntunganQuery($filters, $onlyWeb);
        $total = (clone $target)->count();

        $sub = DB::table('penjualan_detail as pd')
            ->join('barang as b', 'b.id', '=', 'pd.barang_id')
            ->select(
                'pd.penjualan_id',
                DB::raw('SUM((COALESCE(b.harga_2, 0) - COALESCE(b.harga_1, 0)) * pd.qty) as total')
            )
            ->groupBy('pd.penjualan_id');

        $query = DB::table('penjualan as p')
            ->joinSub($target->toBase(), 'target', 'target.id', '=', 'p.id')
            ->leftJoinSub($sub, 'd', 'd.penjualan_id', '=', 'p.id');

        $query->update([
            'p.harga_keuntungan' => DB::raw('COALESCE(d.total, 0)'),
            'p.is_harga_keuntungan_manual' => false,
        ]);

        return (int) $total;
    }
}
