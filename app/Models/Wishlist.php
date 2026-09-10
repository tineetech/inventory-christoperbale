<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlist';

    protected $fillable = [
        'user_id',
        'barang_id',
        'produk_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}