<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'file_resi',
        'kode_penjualan',
        'nomor_resi',
        'nomor_pesanan',
        'nomor_transaksi',
        'dropshipper_id',
        'tanggal',
        'total_harga',
        'harga_discount',
        'shipping_cost',
        'service_fee',
        'subtotal_harga',
        'harga_cair',
        'keterangan',
        'status',
        'order_web',
        'scan_out',
        'is_draft',
        'is_retur',
        'strukprint_status',
        'created_by'
    ];

    public function dropshipper()
    {
        return $this->belongsTo(Dropshipper::class);
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'order_id');
    }

    public function address()
    {
        return $this->hasOne(OrderAddress::class, 'order_id');
    }

    public function shipment()
    {
        return $this->hasOne(OrderShipment::class, 'order_id');
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class, 'order_id');
    }
}