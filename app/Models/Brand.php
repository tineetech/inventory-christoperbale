<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brand';

    protected $fillable = [
        'nama_brand',
        'deskripsi_brand',
        'status_brand',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }
}
