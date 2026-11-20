<?php

namespace App\Exports;

use App\Models\Penjualan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPenjualanExport implements WithMultipleSheets
{
    public function __construct(
        private $penjualan,
        private array $filters,
        private bool $isSuperAdmin = false
    ) {
    }

    public function sheets(): array
    {
        $grouped = collect($this->penjualan)
            ->groupBy(fn($item) => Penjualan::getLaporanMarketplaceGroup($item->nomor_pesanan) ?? 'unknown');

        return collect(Penjualan::LAPORAN_EKSPEDISI_GROUPS)
            ->map(function (string $label, string $group) use ($grouped) {
                $items = $this->sortByResi($grouped->get($group, collect()));

                return new LaporanPenjualanSheetExport(
                    $items,
                    $this->filters,
                    $this->isSuperAdmin,
                    $label
                );
            })
            ->values()
            ->all();
    }

    private function sortByResi(Collection $items): Collection
    {
        return $items
            ->sort(function ($a, $b) {
                $resiA = strtoupper(trim((string) $a->nomor_resi));
                $resiB = strtoupper(trim((string) $b->nomor_resi));

                if ($resiA === '' && $resiB !== '') {
                    return 1;
                }

                if ($resiA !== '' && $resiB === '') {
                    return -1;
                }

                $compare = strnatcasecmp($resiA, $resiB);

                if ($compare !== 0) {
                    return $compare;
                }

                return strcmp((string) $a->kode_penjualan, (string) $b->kode_penjualan);
            })
            ->values();
    }
}
