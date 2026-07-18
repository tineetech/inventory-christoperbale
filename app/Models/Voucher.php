<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'description_full',
        'type',
        'value',
        'minimum_purchase',
        'maximum_discount',
        'quota',
        'used_count',
        'claim_limit_per_user',
        'start_at',
        'end_at',
        'status',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }
}
