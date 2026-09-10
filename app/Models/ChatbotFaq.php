<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ChatbotFaq extends Model
{
    protected $table = 'chatbot_faq';

    protected $fillable = [
        'pertanyaan',
        'jawaban',
        'quick_question',
        'quick_question_order',
        'keywords',
        'is_active',
        'is_sync',
        'synced_at',
        'urutan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quick_question'       => 'boolean',
            'is_active'            => 'boolean',
            'is_sync'              => 'boolean',
            'keywords'             => 'array',
            'synced_at'            => 'datetime',
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

    public function scopeQuick(Builder $query): Builder
    {
        return $query->where('quick_question', true);
    }
}