<table>
    <thead>
        <tr><th colspan="9">Laporan Pembayaran</th></tr>
        <tr>
            <th colspan="9">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Kode Penjualan</th>
            <th>Order ID</th>
            <th>Metode Pembayaran</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th>Dibayar</th>
            <th>Transaction ID</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotal = 0;
        @endphp
        @forelse ($pembayaran as $index => $item)
            @php
                $grandTotal += $item->amount;
                $kodeJual = $item->penjualan
                    ? $item->penjualan->kode_penjualan
                    : ($item->penjualanDraft ? $item->penjualanDraft->kode_penjualan . ' (Draft)' : '-');
                $metodeWeb = strtoupper($item->payment_method ?? $item->payment_type ?? '-');
            @endphp
            <tr>
                <td>#{{ $index + 1 }}</td>
                <td>{{ $kodeJual }}</td>
                <td>{{ $item->order_id_midtrans ?: '-' }}</td>
                <td>{{ $metodeWeb }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->status ?: '-' }}</td>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->paid_at ?: '-' }}</td>
                <td>{{ $item->transaction_id ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="9">Belum ada data pembayaran pada filter ini.</td></tr>
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>
        @endforelse
        {{-- SPASI 2 BARIS --}}
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>

        {{-- GRAND TOTAL --}}
        <tr style="background-color:#c6efce;">
            <td colspan="4"><strong>Grand Total</strong></td>
            <td><strong>{{ $grandTotal }}</strong></td>
            <td colspan="4"></td>
        </tr>
    </tbody>
</table>