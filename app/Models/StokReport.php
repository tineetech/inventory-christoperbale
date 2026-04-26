<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokReport extends Model
{
    protected $table = 'stok_report';

    protected $fillable = [
        'barang_id',
        'dari_tanggal',
        'sampai_tanggal',
        'stok_saat_ini',
        'stok_minimum',
        'status',
        'input_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'dari_tanggal' => 'date',
        'sampai_tanggal' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function inputByUser()
    {
        return $this->belongsTo(Pengguna::class, 'input_by');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(Pengguna::class, 'confirmed_by');
    }
}
