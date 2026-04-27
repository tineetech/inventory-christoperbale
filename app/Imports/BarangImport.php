<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\StokBarang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;

class BarangImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int   $imported = 0;
    public int   $skipped  = 0;
    public array $skipLog  = []; // detail baris yang diskip
    public array $errorLog = []; // detail error teknis

    public function model(array $row)
    {
        $sku  = $row['sku']        ?? null;
        $nama = $row['nama_barang'] ?? null;

        // Skip baris kosong
        if (empty($sku) || empty($nama)) {
            $this->skipped++;
            $this->skipLog[] = [
                'sku'    => $sku ?? '(kosong)',
                'alasan' => 'SKU atau nama barang kosong',
            ];
            return null;
        }

        // Skip kalau SKU duplikat
        if (Barang::where('sku', $sku)->exists()) {
            $this->skipped++;
            $this->skipLog[] = [
                'sku'    => $sku,
                'alasan' => 'SKU sudah terdaftar di database',
            ];
            return null;
        }

        DB::beginTransaction();
        try {
            $satuan = Satuan::where('nama_satuan', 'like', '%' . $row['satuan'] . '%')
                ->orWhere('id', $row['satuan'])
                ->first();

            if (!$satuan) {
                $this->skipped++;
                $this->skipLog[] = [
                    'sku'    => $sku,
                    'alasan' => "Satuan '{$row['satuan']}' tidak ditemukan",
                ];
                DB::rollBack();
                return null;
            }

            $barang = Barang::create([
                'sku'          => $sku,
                'nama_barang'  => $nama,
                'satuan_id'    => $satuan->id,
                'harga_1'      => $row['harga_beli']   ?? 0,
                'harga_2'      => $row['harga_jual']   ?? 0,
                'stok_minimum' => $row['stok_minimum'] ?? 1,
                'keterangan'   => $row['keterangan']   ?? null,
            ]);

            StokBarang::create([
                'barang_id'   => $barang->id,
                'jumlah_stok' => $row['stok_awal'] ?? 0,
            ]);

            DB::commit();
            $this->imported++;

            return $barang;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->skipped++;
            $this->errorLog[] = [
                'sku'    => $sku,
                'alasan' => 'Error teknis: ' . $e->getMessage(),
            ];
            return null;
        }
    }
}