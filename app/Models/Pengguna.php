<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'full_name',
        'email',
        'phone',
        'photo_profile',
        'gender',
        'password',
        'role_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class, 'user_id')->where('is_default', true);
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

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'created_by');
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class, 'user_id');
    }
}