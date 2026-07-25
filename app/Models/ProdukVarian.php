<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukVarian extends Model
{
    protected $table = 'produk_varian';

    protected $fillable = [
        'produk_id',
        'barang_id',
        'warna',
        'size',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
