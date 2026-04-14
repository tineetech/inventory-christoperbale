<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdjustStokDetail extends Model
{
    protected $table = 'adjust_stok_detail';

    protected $fillable = [
        'adjust_stok_id',
        'barang_id',
        'qty_sistem',
        'qty_fisik',
        'selisih'
    ];

    public function adjust()
    {
        return $this->belongsTo(AdjustStok::class,'adjust_stok_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
