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
    <h2>{{ $title ?? 'Laporan Stok' }}</h2>
    <div class="meta">
        Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
        {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>
    <table>
        <thead>
            @if (($tableType ?? 'summary') === 'input')
                <tr>
                    <th>No</th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Stok Saat Ini</th>
                    <th>Stok Minimum</th>
                    <th>Status</th>
                </tr>
            @else
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Qty</th>
                    <th>Stok Sebelum</th>
                    <th>Stok Sesudah</th>
                    <th>Stok Minimum</th>
                    <th>Status</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse ($rows as $item)
                @if (($tableType ?? 'summary') === 'input')
                    <tr>
                        <td>{{ $item->no }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td>{{ $item->stok_saat_ini }}</td>
                        <td>{{ $item->stok_minimum }}</td>
                        <td>{{ ucfirst($item->stok_status) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $item->no }}</td>
                        <td>{{ optional($item->movement_date)->format('d M Y H:i') ?? '-' }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td>{{ str_replace('_', ' ', $item->jenis) }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->stok_sebelum }}</td>
                        <td>{{ $item->stok_sesudah }}</td>
                        <td>{{ $item->stok_minimum }}</td>
                        <td>{{ ucfirst($item->stok_status) }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ ($tableType ?? 'summary') === 'input' ? 7 : 10 }}">
                        {{ ($tableType ?? 'summary') === 'input' ? 'Belum ada data stok pada filter ini.' : 'Belum ada data stok movement pada filter ini.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
