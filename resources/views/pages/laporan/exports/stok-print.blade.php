<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Stok</title>
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
    <h2>Laporan Stok</h2>
    <div class="meta">
        Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
        {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Stok Saat Ini</th>
                <th>Stok Minimum</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barang as $index => $item)
                @php
                    $stokSaatIni = $item->stok->jumlah_stok ?? 0;
                    $minimum = $item->stok_minimum ?? 0;
                    $status = $stokSaatIni <= 0 ? 'Habis' : ($stokSaatIni <= $minimum ? 'Minimum' : 'Aman');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td>{{ $stokSaatIni }}</td>
                    <td>{{ $minimum }}</td>
                    <td>{{ $status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data stok pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
