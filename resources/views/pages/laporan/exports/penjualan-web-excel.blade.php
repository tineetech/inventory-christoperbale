<table>
    <thead>
        <tr><th colspan="10">Laporan Penjualan Web</th></tr>
        <tr>
            <th colspan="10">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Kode Penjualan</th>
            <th>Nomor Resi</th>
            <th>Pembeli</th>
            <th>Alamat Pengiriman</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Total Harga</th>
            <th>Scan Out</th>
            <th>Retur?</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotal = 0;
            $totalQty = 0;
        @endphp
        @forelse ($penjualan as $index => $item)
            @php
                $grandTotal += $item->total_harga;
                $statusWeb = ucfirst($item->status ?? '-');
                $scanWeb = ucfirst($item->scan_out ?? '-');
                $returWeb = match ($item->is_retur) {
                    'yes' => 'Ya',
                    'no' => 'Tidak',
                    default => '-',
                };
                $pembeliWeb = $item->address->recipient_name ?? ($item->user->nama ?? '-');
                $alamatWeb = '';
                if ($item->address) {
                    $alamatWeb = $item->address->address ?? '';
                    if ($item->address->district) $alamatWeb .= ', ' . $item->address->district;
                    if ($item->address->city) $alamatWeb .= ', ' . $item->address->city;
                    if ($item->address->province) $alamatWeb .= ', ' . $item->address->province;
                    if ($item->address->postal_code) $alamatWeb .= ' - ' . $item->address->postal_code;
                }
            @endphp
            <tr>
                <td>#{{ $index + 1 }}</td>
                <td>{{ $item->kode_penjualan }}</td>
                <td>{{ $item->nomor_resi ?: '-' }}</td>
                <td>{{ $pembeliWeb }}</td>
                <td>{{ $alamatWeb ?: '-' }}</td>
                <td>{{ $statusWeb }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->total_harga }}</td>
                <td>{{ $scanWeb }}</td>
                <td>{{ $returWeb }}</td>
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
                    $totalQty += $detail->qty;
                @endphp
                <tr>
                    <td></td>
                    <td>{{ $detail->nomor_resi ?: '-' }}</td>
                    <td>{{ $detail->barang->sku ?? '-' }}</td>
                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
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
            <tr><td colspan="11">Belum ada data penjualan web pada filter ini.</td></tr>
        <tr><td colspan="8"></td></tr>
        <tr><td colspan="8"></td></tr>
        @endforelse
        {{-- SPASI 2 BARIS --}}
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>

        {{-- GRAND TOTAL --}}
        <tr style="background-color:#c6efce;">
            <td colspan="7"><strong>Grand Total</strong></td>
            <td><strong>{{ $grandTotal }}</strong></td>
            <td colspan="2"></td>
        </tr>
        <tr style="background-color:#c6efce;">
            <td colspan="4"><strong>Total Qty Terjual</strong></td>
            <td><strong>{{ $totalQty }}</strong></td>
            <td colspan="5"></td>
        </tr>
    </tbody>
</table>