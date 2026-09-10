<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRateCacheRate extends Model
{
    protected $table = 'shipping_rate_cache_rates';

    protected $fillable = [
        'shipping_rate_cache_id',
        'response_json',
    ];

    protected function casts(): array
    {
        return [
            'response_json' => 'array',
        ];
    }

    public function cache()
    {
        return $this->belongsTo(ShippingRateCache::class, 'shipping_rate_cache_id');
    }
}
