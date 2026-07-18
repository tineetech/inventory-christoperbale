<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    protected $table = 'user_vouchers';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'status',
        'claimed_at',
        'used_at',
        'order_id',
    ];

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function order()
    {
        return $this->belongsTo(Penjualan::class, 'order_id');
    }
}
