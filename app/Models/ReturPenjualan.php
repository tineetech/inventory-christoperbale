<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    protected $table = 'retur_penjualan';

    protected $fillable = [
        'penjualan_id',
        'tanggal_retur',
        'alasan_retur',
        'status',
        'file_path',
        'file_original_name',
        'file_mime',
        'created_by',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function detail()
    {
        return $this->hasMany(ReturPenjualanDetail::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    /**
     * Cek apakah file adalah video berdasarkan mime type.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->file_mime ?? '', 'video/');
    }

    /**
     * Cek apakah file adalah gambar.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->file_mime ?? '', 'image/');
    }

    /**
     * Label badge status.
     */
    public function statusBadge(): string
    {
        return match ($this->status) {
            'pending'  => '<span class="badge badge-warning">Pending</span>',
            'diproses' => '<span class="badge badge-info">Diproses</span>',
            'selesai'  => '<span class="badge badge-success">Selesai</span>',
            'ditolak'  => '<span class="badge badge-danger">Ditolak</span>',
            default    => '<span class="badge badge-secondary">' . $this->status . '</span>',
        };
    }
}