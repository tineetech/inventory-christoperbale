<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table = 'discounts';

    protected $fillable = [
        'name',
        'type',
        'value',
        'start_at',
        'end_at',
    ];

    public function products()
    {
        return $this->belongsToMany(Produk::class, 'discount_product')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function discountProducts()
    {
        return $this->hasMany(DiscountProduct::class);
    }
}
