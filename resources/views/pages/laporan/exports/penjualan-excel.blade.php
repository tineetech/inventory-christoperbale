<table>
    <thead>
        <tr><th colspan="{{ $isSuperAdmin ? 11 : 10 }}">Laporan Penjualan - {{ $sheetTitle ?? 'Semua' }}</th></tr>
        <tr>
            <th colspan="{{ $isSuperAdmin ? 11 : 10 }}">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
                Mode Harga: {{ ($filters['mode_harga'] ?? 'harga_reseller') === 'harga_hpp' ? 'Harga HPP' : 'Harga Reseller' }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Kode Penjualan</th>
            <th>Nomor Resi</th>
            <th>No. Pesanan</th>
            {{-- <th>No. Transaksi</th> --}}
            <th>Dropshipper</th>
            <th>Tanggal</th>
            <th>Total Harga</th>
            <th>Harga Cair</th>
            @if ($isSuperAdmin)
            <th>Keuntungan</th>
            @endif
            {{-- <th>Scan Out</th> --}}
            <th>Retur ?</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotal = 0;
            $grandTotalHargaCair = 0;
            $grandTotalKeuntungan = 0;
            $totalQty = 0;
            $modeHarga = $filters['mode_harga'] ?? 'harga_reseller';
        @endphp
        @forelse ($penjualan as $index => $item)
            @php
                $isRetur = $item->is_retur === 'yes';
                $totalHarga = $isRetur ? 0 : $item->totalHargaByMode($modeHarga);
                $hargaCair = $isRetur ? 0 : ($item->harga_cair ?? 0);
                $keuntungan = $isRetur ? 0 : ($item->harga_keuntungan ?? 0);
                $grandTotal += $totalHarga;
                $grandTotalHargaCair += $hargaCair;
                $grandTotalKeuntungan += $keuntungan;
                $scanOutLabel = $item->scan_out ? ucfirst($item->scan_out) : '-';
                $draftLabel = match ($item->is_draft) {
                    'yes' => 'Ya',
                    'no' => 'Tidak',
                    default => '-',
                };
            @endphp
            <tr>
                <td>#{{ $index + 1 }}</td>
                <td>{{ $item->kode_penjualan }}</td>
                <td>{{ $item->nomor_resi ?: '-' }}</td>
                <td data-type="s">{{ $item->nomor_pesanan ?: '-' }}</td>
                {{-- <td>{{ $item->nomor_transaksi ?: '-' }}</td> --}}
                <td>{{ $item->dropshipper->nama ?? '-' }}</td>
                <td>{{ $item->tanggal }}</td>
                <td data-format='"Rp" #,##0'>{{ $totalHarga }}</td>
                {{-- <td>{{ $item->harga_cair }}</td> --}}
                                    
                <td style="white-space: nowrap;" data-format='"Rp" #,##0'>
                    {{ $isRetur || $item->harga_cair !== null ? $hargaCair : '' }}
                </td>
                @if ($isSuperAdmin)
                <td style="white-space: nowrap;" data-format='"Rp" #,##0'>{{ $keuntungan }}</td>
                @endif
                {{-- <td>{{ $scanOutLabel }}</td> --}}
                <td>{{ $item->is_retur }}</td>
                <td>{{ $item->keterangan ?: '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="{{ $isSuperAdmin ? 10 : 9 }}"><strong>Detail Barang</strong></td>
            </tr>
            <tr>
                <td></td>
                <td>No Resi</td>
                <td>SKU</td>
                <td>Nama Barang</td>
                {{-- <td>Stok Sekarang</td>--}}
                <td>Qty Terjual</td>
                <td>Harga</td>
                <td>Subtotal</td>
                <td colspan="{{ $isSuperAdmin ? 4 : 3 }}"></td>
            </tr>
            @php
                $totalDetail = 0;
            @endphp
            @forelse ($item->detail as $detail)
                @php
                    $hargaDetail = $detail->hargaByMode($modeHarga);
                    $qtyTerjual = $isRetur ? 0 : $detail->qty;
                    $subtotalDetail = $isRetur ? 0 : ($detail->qty * $hargaDetail);
                    $totalDetail += $subtotalDetail;
                    $totalQty += $qtyTerjual;
                @endphp
                <tr>
                    <td></td>
                    <td>{{ $detail->nomor_resi ?: '-' }}</td>
                    <td>{{ $detail->barang->sku ?? '-' }}</td>
                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                    {{-- <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td> --}}
                    <td>{{ $qtyTerjual }}</td>
                    <td data-format='"Rp" #,##0'>{{ $hargaDetail }}</td>
                    <td data-format='"Rp" #,##0'>{{ $subtotalDetail }}</td>
                    <td colspan="{{ $isSuperAdmin ? 4 : 3 }}"></td>
                </tr>
            @empty
                <tr>
                    <td></td>
                    <td colspan="{{ $isSuperAdmin ? 10 : 9 }}">Belum ada detail barang pada transaksi ini.</td>
                </tr>
            @endforelse
            @if ($item->detail->isNotEmpty())
                <tr>
                    <td></td>
                    <td colspan="4">Total Penjualan</td>
                    <td data-format='"Rp" #,##0'>{{ $totalDetail }}</td>
                    <td colspan="{{ $isSuperAdmin ? 5 : 4 }}"></td>
                </tr>
            @endif
        @empty
            <tr><td colspan="{{ $isSuperAdmin ? 11 : 10 }}">Belum ada data penjualan untuk grup {{ $sheetTitle ?? 'ini' }}.</td></tr>
        <tr><td colspan="8"></td></tr>
        <tr><td colspan="8"></td></tr>
        @endforelse
        {{-- SPASI 2 BARIS --}}
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>

        {{-- GRAND TOTAL --}}
        <tr>
            <td colspan="6"><strong>Grand Total</strong></td>
            <td data-format='"Rp" #,##0'><strong>{{ $grandTotal }}</strong></td>
            <td data-format='"Rp" #,##0'><strong>{{ $grandTotalHargaCair }}</strong></td>
            @if ($isSuperAdmin)
            <td data-format='"Rp" #,##0'><strong>{{ $grandTotalKeuntungan }}</strong></td>
            @endif
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="4"><strong>Total Qty Terjual</strong></td>
            <td><strong>{{ $totalQty }}</strong></td>
            <td colspan="{{ $isSuperAdmin ? 6 : 5 }}"></td>
        </tr>
    </tbody>
</table>
