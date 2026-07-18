<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $table = 'order_address';

    protected $fillable = [
        'order_id',
        'recipient_name',
        'phone',
        'province',
        'city',
        'district',
        'postal_code',
        'address',
        'label',
        'latitude',
        'longitude',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'order_id');
    }
}
