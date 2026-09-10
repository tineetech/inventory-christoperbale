<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus duplikat yang sudah ada (keep yang paling lama)
        $duplicates = DB::table('user_vouchers')
            ->select('user_id', 'voucher_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'voucher_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('user_vouchers')
                ->where('user_id', $dup->user_id)
                ->where('voucher_id', $dup->voucher_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->unique(['user_id', 'voucher_id'], 'user_vouchers_user_voucher_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dropUnique('user_vouchers_user_voucher_unique');
        });
    }
};