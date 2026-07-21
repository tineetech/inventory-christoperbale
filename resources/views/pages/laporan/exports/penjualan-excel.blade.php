<table>
    <thead>
        <tr><th colspan="8">Laporan Penjualan</th></tr>
        <tr>
            <th colspan="8">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
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
            {{-- <th>Scan Out</th> --}}
            {{-- <th>Draft</th> --}}
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotal = 0;
            $grandTotalHargaCair = 0;
        @endphp
        @forelse ($penjualan as $index => $item)
            @php
                $grandTotal += $item->total_harga;
                $grandTotalHargaCair += $item->harga_cair ?? 0;
            @endphp
            @php
                $scanOutLabel = $item->scan_out ? ucfirst($item->scan_out) : '-';
                $draftLabel = match ($item->is_draft) {
                    'yes' => 'Ya',
                    'no' => 'Tidak',
                    default => '-',
                };
            @endphp
            <tr{{ $item->keterangan === 'sedang transit' ? ' style="background-color:#fff3e0"' : '' }}>
                <td>#{{ $index + 1 }}</td>
                <td>{{ $item->kode_penjualan }}</td>
                <td>{{ $item->nomor_resi ?: '-' }}</td>
                <td>{{ $item->nomor_pesanan ?: '-' }}</td>
                {{-- <td>{{ $item->nomor_transaksi ?: '-' }}</td> --}}
                <td>{{ $item->dropshipper->nama ?? '-' }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->total_harga }}</td>
                <td>{{ $item->harga_cair }}</td>
                {{-- <td>{{ $scanOutLabel }}</td>
                <td>{{ $draftLabel }}</td> --}}
                <td>{{ $item->keterangan ?: '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="10"><strong>Detail Barang</strong></td>
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
                <td colspan="3"></td>
            </tr>
            @php
                $totalDetail = 0;
            @endphp
            @forelse ($item->detail as $detail)
                @php
                    $totalDetail += $detail->subtotal;
                @endphp
                <tr>
                    <td></td>
                    <td>{{ $detail->nomor_resi ?: '-' }}</td>
                    <td>{{ $detail->barang->sku ?? '-' }}</td>
                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                    {{-- <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td> --}}
                    <td>{{ $detail->qty }}</td>
                    <td>{{ $detail->harga }}</td>
                    <td>{{ $detail->subtotal }}</td>
                    <td colspan="3"></td>
                </tr>
            @empty
                <tr>
                    <td></td>
                    <td colspan="10">Belum ada detail barang pada transaksi ini.</td>
                </tr>
            @endforelse
            @if ($item->detail->isNotEmpty())
                <tr>
                    <td></td>
                    <td colspan="4">Total Penjualan</td>
                    <td>{{ $totalDetail }}</td>
                    <td colspan="3"></td>
                </tr>
            @endif
        @empty
            <tr><td colspan="11">Belum ada data penjualan pada filter ini.</td></tr>
        <tr><td colspan="8"></td></tr>
        <tr><td colspan="8"></td></tr>
        @endforelse
        {{-- SPASI 2 BARIS --}}
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>

        {{-- GRAND TOTAL --}}
        <tr>
            <td colspan="6"><strong>Grand Total</strong></td>
            <td><strong>{{ $grandTotal }}</strong></td>
            <td><strong>{{ $grandTotalHargaCair }}</strong></td>
        </tr>
    </tbody>
</table>
