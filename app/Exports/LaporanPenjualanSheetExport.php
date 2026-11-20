<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPenjualanSheetExport implements FromView, WithTitle, WithStyles, WithEvents
{
    public function __construct(
        private Collection $penjualan,
        private array $filters,
        private bool $isSuperAdmin,
        private string $sheetTitle
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.penjualan-excel', [
            'penjualan' => $this->penjualan,
            'filters' => $this->filters,
            'isSuperAdmin' => $this->isSuperAdmin,
            'sheetTitle' => $this->sheetTitle,
        ]);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $this->isSuperAdmin ? 'K' : 'J';

        $sheet->getStyle("A1:{$lastColumn}2")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
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

                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(18);
                $sheet->getColumnDimension('J')->setWidth($this->isSuperAdmin ? 18 : 35);

                if ($this->isSuperAdmin) {
                    $sheet->getColumnDimension('K')->setWidth(35);
                }

                $highestRow = $sheet->getHighestRow();

                $returKodes = collect($this->penjualan)
                    ->filter(fn($item) => $item->is_retur === 'yes')
                    ->pluck('kode_penjualan')
                    ->map(fn($kode) => trim((string) $kode))
                    ->all();

                $transitKodes = collect($this->penjualan)
                    ->filter(fn($item) => $item->is_retur !== 'yes' && $item->keterangan === 'sedang transit')
                    ->pluck('kode_penjualan')
                    ->map(fn($kode) => trim((string) $kode))
                    ->all();

                $lastColumn = $this->isSuperAdmin ? 'K' : 'J';

                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell("A$row")->getValue();

                    if (is_string($cellValue) && str_contains($cellValue, '#')) {
                        $kode = trim((string) $sheet->getCell("B$row")->getValue());
                        $isRetur = in_array($kode, $returKodes, true);
                        $isTransit = in_array($kode, $transitKodes, true);

                        $fillColor = $isRetur ? 'FFC7CE' : ($isTransit ? 'FFF3E0' : 'E7E6E6');
                        $fontColor = $isRetur ? '9C0006' : '000000';

                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => $fontColor],
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => $fillColor],
                            ],
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => 'medium',
                                    'color' => ['rgb' => '999999'],
                                ],
                            ],
                        ]);

                        continue;
                    }

                    $rowText = trim((string) $cellValue . ' ' . (string) $sheet->getCell("B$row")->getValue());

                    if (str_contains($rowText, 'Grand Total')) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'C6EFCE'],
                            ],
                            'borders' => [
                                'top' => [
                                    'borderStyle' => 'medium',
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);
                    } elseif (str_contains($rowText, 'Total Qty Terjual')) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'D9EAD3'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
