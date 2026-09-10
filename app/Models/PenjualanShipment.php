<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanShipment extends Model
{
    protected $table = 'penjualan_shipment';

    protected $fillable = [
        'penjualan_id',
        'penjualan_draft_id',
        'courier',
        'service',
        'tracking_number',
        'shipping_cost',
        'estimation_days',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function penjualanDraft()
    {
        return $this->belongsTo(PenjualanDraft::class);
    }
}
