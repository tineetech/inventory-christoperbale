<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $voucher = Voucher::updateOrCreate(['code' => 'WELCOME10'], [
            'name'             => 'Diskon 10%',
            'description'      => 'Voucher diskon 10% untuk pengguna baru',
            'description_full' => '<p>Voucher selamat datang untuk pengguna baru di aplikasi kami.</p><p>Nikmati diskon <strong>10%</strong> untuk setiap pembelian pertama Anda dengan ketentuan berikut:</p><ul><li>Minimal belanja Rp50.000</li><li>Maksimal diskon Rp50.000</li><li>Berlaku untuk semua produk</li><li>Tidak dapat digabungkan dengan voucher lain</li></ul>',
            'type'             => 'percent',
            'value'            => 10,
            'minimum_purchase' => 50000,
            'maximum_discount' => 50000,
            'quota'            => 100,
            'used_count'       => 0,
            'claim_limit_per_user' => 1,
            'start_at'         => Carbon::now(),
            'end_at'           => Carbon::now()->addMonth(),
            'status'           => 'active',
            'created_by'       => null,
        ]);

        $user = Pengguna::where('email', 'justinebogor0609@gmail.com')->first();

        if ($user) {
            UserVoucher::create([
                'user_id'    => $user->id,
                'voucher_id' => $voucher->id,
                'status'     => 'unused',
                'claimed_at' => Carbon::now(),
            ]);
        }
    }
}

