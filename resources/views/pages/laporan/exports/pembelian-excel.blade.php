<table>
    <thead>
        <tr><th colspan="7">Laporan Pembelian</th></tr>
        <tr>
            <th colspan="7">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Kode Pembelian</th>
            <th>Supplier</th>
            <th>Tanggal</th>
            <th>Total Harga</th>
            <th>Dibuat Oleh</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($pembelian as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode_pembelian }}</td>
                <td>{{ $item->supplier->nama_supplier ?? '-' }}</td>
                <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                <td>{{ $item->total_harga }}</td>
                <td>{{ $item->user->nama ?? '-' }}</td>
                <td>{{ $item->keterangan ?: '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="6"><strong>Detail Barang</strong></td>
            </tr>
            <tr>
                <td></td>
                <td>SKU</td>
                <td>Nama Barang</td>
                <td>Stok Sekarang</td>
                <td>Qty Dibeli</td>
                <td>Harga</td>
                <td>Subtotal</td>
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
                    <td>{{ $detail->barang->sku ?? '-' }}</td>
                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                    <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td>
                    <td>{{ $detail->qty }}</td>
                    <td>{{ $detail->harga }}</td>
                    <td>{{ $detail->subtotal }}</td>
                </tr>
            @empty
                <tr>
                    <td></td>
                    <td colspan="6">Belum ada detail barang pada transaksi ini.</td>
                </tr>
            @endforelse
            @if ($item->detail->isNotEmpty())
                <tr>
                    <td></td>
                    <td colspan="5">Total Pembelian</td>
                    <td>{{ $totalDetail }}</td>
                </tr>
            @endif
        @empty
            <tr><td colspan="7">Belum ada data pembelian pada filter ini.</td></tr>
        @endforelse
    </tbody>
</table>
