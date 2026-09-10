<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $attributes = [
        'is_read' => false,
    ];

    protected $fillable = [
        'judul',
        'isi',
        'tipe',
        'link',
        'payload',
        'user_id',
        'is_read',
        'read_at',
        'created_by'
    ];

    protected function casts(): array
    {
        return [
            'payload'  => 'array',
            'is_read'  => 'boolean',
            'read_at'  => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(Pengguna::class, 'created_by');
    }

    /**
     * Notifikasi yang ditujukan untuk user tertentu
     * atau yang bersifat broadcast (user_id null).
     */
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where(fn($q) => $q->where('user_id', $userId)->orWhereNull('user_id'));
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }
}