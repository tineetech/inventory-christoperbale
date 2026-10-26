<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanPenjualanExport implements FromView, WithStyles, WithEvents
{
    public function __construct(
        private $penjualan,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.penjualan-excel', [
            'penjualan' => $this->penjualan,
            'filters' => $this->filters,
        ]);
    }

    
    public function styles(Worksheet $sheet)
    {
    
        // Center text + bold
        $sheet->getStyle('A1:I2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // Style header row ke-3 (border + warna hijau)
        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // putih
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '006400'], // hijau tua
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '006400'],
                ],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);



        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // $sheet = $event->sheet->getDelegate();
                $sheet = $event->sheet->getDelegate();

// Set lebar kolom manual
$sheet->getColumnDimension('A')->setWidth(8);   // No
$sheet->getColumnDimension('B')->setWidth(22);  // Kode Penjualan
$sheet->getColumnDimension('C')->setWidth(20);  // Nomor Resi
$sheet->getColumnDimension('D')->setWidth(22);  // Dropshipper
$sheet->getColumnDimension('E')->setWidth(18);  // Tanggal
$sheet->getColumnDimension('F')->setWidth(18);  // Total Harga
$sheet->getColumnDimension('G')->setWidth(18);  // Harga Cair
$sheet->getColumnDimension('H')->setWidth(10);  // Retur?
$sheet->getColumnDimension('I')->setWidth(35);  // Keterangan
                $highestRow = $sheet->getHighestRow();

                for ($row = 1; $row <= $highestRow; $row++) {

                    $cellValue = $sheet->getCell("A$row")->getValue();

                    // Deteksi baris header transaksi (yang ada tanda #1 | kode)
                    if (is_string($cellValue) && str_contains($cellValue, '#')) {

                        // Baris penjualan retur diberi highlight merah muda
                        $isRetur = trim((string) $sheet->getCell("H{$row}")->getValue()) === 'Ya';

                        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => $isRetur ? 'A30000' : '000000'],
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => $isRetur ? 'FFE0E0' : 'E7E6E6'], // retur: merah muda, lainnya: abu soft
                            ],
                            'borders' => [
                                'outline' => [ // border luar saja
                                    'borderStyle' => 'medium',



                                    'color' => ['rgb' => '999999'],
                                ],
                            ],
                        ]);
                    }
                }


                $sheet->getStyle("A{$highestRow}:I{$highestRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'D9EAD3'], // hijau muda
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => 'medium',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }
        ];
    }
}
