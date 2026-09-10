<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'receiver_name',
        'phone',
        'province',
        'city',
        'district',
        'postal_code',
        'address',
        'catatan',
        'latitude',
        'longitude',
        'label',
        'is_default',
        'area_id',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }
}
