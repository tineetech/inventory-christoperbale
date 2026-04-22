<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanPembelianExport implements FromView
{
    public function __construct(
        private $pembelian,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.pembelian-excel', [
            'pembelian' => $this->pembelian,
            'filters' => $this->filters,
        ]);
    }
}
