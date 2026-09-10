<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRateCacheItem extends Model
{
    protected $fillable = [
        'shipping_rate_cache_id',
        'barang_id',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'value',
    ];

    public function cache()
    {
        return $this->belongsTo(ShippingRateCache::class, 'shipping_rate_cache_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
