<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'brand_id',
        'nama_produk',
        'slug',
        'deskripsi',
        'harga_normal',
        'harga_diskon',
        'status',
    ];

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function varian()
    {
        return $this->hasMany(ProdukVarian::class);
    }

    public function foto()
    {
        return $this->hasMany(ProdukFoto::class);
    }

    public function fotoUtama()
    {
        return $this->hasOne(ProdukFoto::class)->where('is_utama', true);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_product')
            ->withPivot('status')
            ->withTimestamps();
    }
}
