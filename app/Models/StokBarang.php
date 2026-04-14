<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class StokBarang extends Model
{
    protected $table = 'stok_barang';

    protected $fillable = [
        'barang_id',
        'jumlah_stok'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}