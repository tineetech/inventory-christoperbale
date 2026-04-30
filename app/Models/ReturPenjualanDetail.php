<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPenjualanDetail extends Model
{
    protected $table = 'retur_penjualan_detail';

    protected $fillable = [
        'retur_penjualan_id',
        'penjualan_detail_id',
        'barang_id',
        'qty_retur',
        'keterangan',
    ];

    public function returPenjualan()
    {
        return $this->belongsTo(ReturPenjualan::class);
    }

    public function penjualanDetail()
    {
        return $this->belongsTo(PenjualanDetail::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}