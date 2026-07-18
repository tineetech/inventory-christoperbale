<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    protected $table = 'order_shipment';

    protected $fillable = [
        'order_id',
        'courier',
        'service',
        'tracking_number',
        'shipping_cost',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'order_id');
    }
}
