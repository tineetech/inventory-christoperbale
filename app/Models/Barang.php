<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'sku',
        'nama_barang',
        'satuan_id',
        'harga_1',
        'harga_2',
        'stok_minimum',
        'keterangan'
    ];

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    public function stok()
    {
        return $this->hasOne(StokBarang::class);
    }

    public function pembelianDetail()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    public function penjualanDetail()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function stokMovement()
    {
        return $this->hasMany(StokMovement::class);
    }
}