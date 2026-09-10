<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppRiwayat extends Model
{
    protected $table = 'hpp_riwayat';

    protected $fillable = [
        'barang_id',
        'hpp_lama',
        'hpp_baru',
        'tanggal',
        'created_by'
    ];

    protected $casts = [
        'hpp_lama' => 'decimal:2',
        'hpp_baru' => 'decimal:2',
        'tanggal'  => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function detail()
    {
        return $this->hasMany(HppRiwayatDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }
}