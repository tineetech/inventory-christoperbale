<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dropshipper extends Model
{
    protected $table = 'dropshipper';

    protected $fillable = [
        'nama',
        'no_telp',
        'alamat',
        'keterangan'
    ];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }
}