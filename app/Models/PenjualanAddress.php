<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanAddress extends Model
{
    protected $table = 'penjualan_address';

    protected $fillable = [
        'penjualan_id',
        'penjualan_draft_id',
        'recipient_name',
        'phone',
        'province',
        'city',
        'district',
        'postal_code',
        'address',
        'catatan',
        'label',
        'latitude',
        'longitude',
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
