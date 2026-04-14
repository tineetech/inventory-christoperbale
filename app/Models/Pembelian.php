<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';

    protected $fillable = [
        'kode_pembelian',
        'supplier_id',
        'tanggal',
        'total_harga',
        'keterangan',
        'created_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class,'created_by');
    }

    public function detail()
    {
        return $this->hasMany(PembelianDetail::class);
    }
}