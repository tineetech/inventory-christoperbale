<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_faq', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan');
            $table->text('jawaban');
            $table->boolean('quick_question')->default(false);
            $table->unsignedInteger('quick_question_order')->nullable();
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sync')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->unsignedInteger('urutan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'quick_question']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_faq');
    }
};