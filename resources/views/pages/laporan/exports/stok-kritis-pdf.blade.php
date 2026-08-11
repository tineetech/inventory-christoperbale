<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Kritis PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            margin-bottom: 18px;
        }

        .header h2 {
            margin: 0 0 6px;
        }

        .meta {
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background: #f3f3f3;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Stok Kritis</h2>
        <div class="meta">
            Stok dibawah minimum dan dibawah 10 yang perlu di restock |
            Filter Status: {{ $filters['status'] ?? 'semua' }} |
            Dicetak: {{ now()->format('d M Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="14%">SKU</th>
                <th width="30%">Nama Barang</th>
                <th width="10%">Satuan</th>
                <th width="13%">Stok Minimum</th>
                <th width="13%">Stok Sekarang</th>
                <th width="15%">Status</th>
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