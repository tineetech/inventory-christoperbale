<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDraftItem extends Model
{
    protected $table = 'penjualan_draft_items';

    protected $fillable = [
        'penjualan_draft_id',
        'barang_id',
        'qty',
        'harga',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga'    => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function penjualanDraft()
    {
        return $this->belongsTo(PenjualanDraft::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
