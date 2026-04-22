<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Pembelian</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        h2 { margin-bottom: 4px; }
        .meta { margin-bottom: 20px; color: #666; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d9d9d9; padding: 8px; font-size: 12px; vertical-align: top; }
        th { background: #f5f5f5; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <h2>Laporan Pembelian</h2>
    <div class="meta">
        Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
        {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>
    <table>
        <thead>
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
                    <td>{{ date('d M Y', strtotime($item->tanggal)) }}</td>
                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td>{{ $item->user->nama ?? '-' }}</td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data pembelian pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
