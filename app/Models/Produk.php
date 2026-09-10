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
        'is_popular',
        'is_newproduct',
        'berat_gram',
        'panjang_cm',
        'lebar_cm',
        'tinggi_cm',
    ];

    protected function casts(): array
    {
        return [
            'is_popular'    => 'boolean',
            'is_newproduct' => 'boolean',
            'berat_gram'    => 'integer',
            'panjang_cm'    => 'integer',
            'lebar_cm'      => 'integer',
            'tinggi_cm'     => 'integer',
        ];
    }

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
