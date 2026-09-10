<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanPembayaranExport implements FromView, WithStyles, WithEvents
{
    public function __construct(
        private $pembayaran,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.pembayaran-excel', [
            'pembayaran' => $this->pembayaran,
            'filters' => $this->filters,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Title rows
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

        // Header row ke-3
        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '006400'],
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
                $sheet = $event->sheet->getDelegate();

                $sheet->getColumnDimension('A')->setWidth(8);   // No
                $sheet->getColumnDimension('B')->setWidth(22);  // Kode Penjualan
                $sheet->getColumnDimension('C')->setWidth(24);  // Order ID
                $sheet->getColumnDimension('D')->setWidth(22);  // Metode
                $sheet->getColumnDimension('E')->setWidth(18);  // Jumlah
                $sheet->getColumnDimension('F')->setWidth(14);  // Status
                $sheet->getColumnDimension('G')->setWidth(18);  // Dibuat
                $sheet->getColumnDimension('H')->setWidth(18);  // Dibayar
                $sheet->getColumnDimension('I')->setWidth(30);  // Transaction ID

                $highestRow = $sheet->getHighestRow();

                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell("A$row")->getValue();

                    if (is_string($cellValue) && str_contains($cellValue, '#')) {
                        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'E7E6E6'],
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
                        'startColor' => ['rgb' => 'D9EAD3'],
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