<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class BarangTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['SKU001', 'Contoh Barang 1', 'PCS', 15000, 25000, 1, 10, 'Keterangan opsional'],
            ['SKU002', 'Contoh Barang 2', 'PCS', 20000, 35000, 1, 5,  ''],
        ];
    }

    public function headings(): array
    {
        return ['sku', 'nama_barang', 'satuan', 'harga_beli', 'harga_jual', 'stok_minimum', 'stok_awal', 'keterangan'];
    }

    public function styles(Worksheet $sheet)
    {
        // Auto width semua kolom berdasarkan konten
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())
                  ->setAutoSize(true);
        }

        return [
            // Header row: bold + background hijau + teks putih
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF28A745'],
                ],
            ],
        ];
    }
}