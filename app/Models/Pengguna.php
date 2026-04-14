<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Pengguna extends Model
{
    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class,'created_by');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class,'created_by');
    }

    public function stokMovement()
    {
        return $this->hasMany(StokMovement::class,'created_by');
    }
}