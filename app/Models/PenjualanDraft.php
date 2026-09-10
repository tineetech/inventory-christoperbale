<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDraft extends Model
{
    protected $table = 'penjualan_draft';

    protected $fillable = [
        'kode_penjualan',
        'tanggal',
        'total_harga',
        'harga_discount',
        'shipping_cost',
        'subtotal_harga',
        'keterangan',
        'order_web',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'        => 'date',
            'total_harga'    => 'decimal:2',
            'harga_discount' => 'decimal:2',
            'shipping_cost'  => 'decimal:2',
            'subtotal_harga' => 'decimal:2',
            'order_web'      => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(PenjualanDraftItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'penjualan_draft_id');
    }

    public function address()
    {
        return $this->hasOne(PenjualanAddress::class, 'penjualan_draft_id');
    }

    public function shipment()
    {
        return $this->hasOne(PenjualanShipment::class, 'penjualan_draft_id');
    }
}
