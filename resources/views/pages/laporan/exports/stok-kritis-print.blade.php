<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Stok Kritis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #222;
        }

        h2 {
            margin-bottom: 4px;
        }

        .meta {
            margin-bottom: 20px;
            color: #666;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 8px;
            font-size: 12px;
            vertical-align: top;
        }

        th {
            background: #f5f5f5;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <h2>Laporan Stok Kritis</h2>
    <div class="meta">
        Stok dibawah minimum dan dibawah 10 yang perlu di restock |
        Filter Status: {{ $filters['status'] ?? 'semua' }} |
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Stok Minimum</th>
                <th>Stok Sekarang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stokKritis as $index => $item)
                @php
                    $stokSaatIni = $item->stok->jumlah_stok ?? 0;
                    $minimum = $item->stok_minimum ?? 0;
                    $status = $stokSaatIni <= 0
                        ? 'Habis'
                        : ($stokSaatIni < 10 && $stokSaatIni > $minimum
                            ? 'Dibawah 10'
                            : 'Kritis');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td class="text-center">{{ $minimum }}</td>
                    <td class="text-center">{{ $stokSaatIni }}</td>
                    <td>{{ $status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Semua stok aman.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>