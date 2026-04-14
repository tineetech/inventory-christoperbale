<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdjustStok extends Model
{
    protected $table = 'adjust_stok';

    protected $fillable = [
        'kode_adjust',
        'tanggal',
        'keterangan',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(Pengguna::class,'created_by');
    }

    public function detail()
    {
        return $this->hasMany(AdjustStokDetail::class);
    }
}