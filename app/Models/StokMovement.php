<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMovement extends Model
{
    protected $table = 'stok_movement';

    protected $fillable = [
        'barang_id',
        'jenis',
        'qty',
        'stok_sebelum',
        'stok_sesudah',
        'referensi_tipe',
        'referensi_id',
        'keterangan',
        'created_by'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class,'created_by');
    }
}