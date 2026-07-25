<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountProduct extends Model
{
    protected $table = 'discount_product';

    protected $fillable = [
        'discount_id',
        'product_id',
        'status',
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function product()
    {
        return $this->belongsTo(Produk::class, 'product_id');
    }
}
