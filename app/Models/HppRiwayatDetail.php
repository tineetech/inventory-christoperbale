<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppRiwayatDetail extends Model
{
    protected $table = 'hpp_riwayat_detail';

    protected $fillable = [
        'hpp_riwayat_id',
        'nama_biaya',
        'harga',
        'tanggal',
        'created_by'
    ];

    protected $casts = [
        'harga'   => 'decimal:2',
        'tanggal' => 'datetime',
    ];

    public function hppRiwayat()
    {
        return $this->belongsTo(HppRiwayat::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }
}