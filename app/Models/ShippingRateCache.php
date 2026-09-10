<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRateCache extends Model
{
    protected $fillable = [
        'user_id',
        'origin_area_id',
        'destination_area_id',
        'origin_postal_code',
        'destination_postal_code',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(ShippingRateCacheItem::class);
    }

    public function rates()
    {
        return $this->hasMany(ShippingRateCacheRate::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class);
    }
}
