<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Laporan Pembayaran</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        h2 { margin-bottom: 4px; }
        .meta { margin-bottom: 20px; color: #666; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d9d9d9; padding: 8px; font-size: 10px; vertical-align: top; }
        th { background: #f5f5f5; }
        tr.total td { background: #c6efce; font-weight: bold; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <h2>Laporan Pembayaran</h2>
    <div class="meta">
        Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
        {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>
    <table>
        <thead>
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
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $kodeJual }}</td>
                    <td>{{ $item->order_id_midtrans ?: '-' }}</td>
                    <td>{{ $metodeWeb }}</td>
                    <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($item->status ?? '-') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->paid_at ? \Carbon\Carbon::parse($item->paid_at)->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $item->transaction_id ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Belum ada data pembayaran pada filter ini.</td>
                </tr>
            @endforelse
            @if ($pembayaran->isNotEmpty())
                <tr class="total">
                    <td colspan="4" style="text-align: right;">Grand Total</td>
                    <td>Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td colspan="4"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>