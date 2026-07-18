<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'order_id',
        'payment_provider',
        'payment_method',
        'transaction_id',
        'gross_amount',
        'payment_status',
        'paid_at',
        'expired_at',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'order_id');
    }
}
