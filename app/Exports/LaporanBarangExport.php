<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanBarangExport implements FromView
{
    public function __construct(
        private $barang,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.barang-excel', [
            'barang' => $this->barang,
            'filters' => $this->filters,
        ]);
    }
}
