<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'kode_penjualan',
        'nomor_resi',
        'nomor_pesanan',
        'nomor_transaksi',
        'dropshipper_id',
        'tanggal',
        'total_harga',
        'keterangan',
        'created_by'
    ];

    public function dropshipper()
    {
        return $this->belongsTo(Dropshipper::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class,'created_by');
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class);
    }
}