<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    protected $table = 'penjualan_detail';

    protected $fillable = [
        'penjualan_id',
        'nomor_resi',
        'nomor_pesanan',
        'nomor_transaksi',
        'barang_id',
        'qty',
        'harga',
        'subtotal'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Harga detail sesuai mode laporan (visualisasi saja, tidak mengubah data).
     * harga_reseller → barang.harga_2, harga_hpp → barang.harga_1.
     */
    public function hargaByMode(string $mode = 'harga_reseller'): float
    {
        $barang = $this->barang;

        $harga = $mode === 'harga_hpp'
            ? ($barang->harga_1 ?? null)
            : ($barang->harga_2 ?? null);

        if ($harga === null) {
            $harga = $this->harga;
        }

        return (float) ($harga ?? 0);
    }

    public function subtotalByMode(string $mode = 'harga_reseller'): float
    {
        return (float) ($this->qty ?? 0) * $this->hargaByMode($mode);
    }
}