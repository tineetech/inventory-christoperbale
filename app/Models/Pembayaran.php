<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'penjualan_id',
        'penjualan_draft_id',
        'order_id_midtrans',
        'transaction_id',
        'snap_token',
        'payment_method',
        'payment_type',
        'alasan_override_pembayaran',
        'amount',
        'status',
        'proof_img',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'paid_at'    => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function penjualanDraft()
    {
        return $this->belongsTo(PenjualanDraft::class, 'penjualan_draft_id');
    }
}
