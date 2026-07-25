<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProdukFoto extends Model
{
    protected $table = 'produk_foto';

    protected $fillable = [
        'produk_id',
        'foto',
        'urutan',
        'is_utama',
    ];

    protected static function booted()
    {
        static::deleted(function ($foto) {
            Storage::disk('public')->delete($foto->foto);
        });
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
