<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Banner extends Model
{
    protected $table = 'banner';

    protected $fillable = [
        'title',
        'gambar',
        'catatan',
        'is_active',
        'urutan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}