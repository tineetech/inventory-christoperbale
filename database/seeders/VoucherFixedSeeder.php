<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VoucherFixedSeeder extends Seeder
{
    public function run(): void
    {
        $voucher = Voucher::updateOrCreate(['code' => 'FIXED20K'], [
            'name'             => 'Potongan Rp20.000',
            'description'      => 'Voucher potongan harga tetap Rp20.000',
            'description_full' => '<p>Voucher potongan harga tetap sebesar <strong>Rp20.000</strong> untuk belanja Anda.</p><p>Syarat &amp; Ketentuan:</p><ol><li>Minimal belanja Rp100.000</li><li>Potongan langsung dikurangi dari total belanja</li><li>Hanya berlaku untuk 1x transaksi</li><li>Masa berlaku sesuai dengan tanggal yang tertera</li></ol>',
            'type'             => 'fixed',
            'value'            => 20000,
            'minimum_purchase' => 100000,
            'maximum_discount' => null,
            'quota'            => 50,
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
