<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Barang</title>
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

        .barcode-box {
            text-align: center;
        }

        .barcode-box img {
            width: 180px;
            max-width: 100%;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <h2>Laporan Barang</h2>
    <div class="meta">
        Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
        {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
        Filter Stok: {{ ucfirst($filters['stok']) }} |
        Dicetak: {{ now()->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Barcode</th>
                <th>Tanggal</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Keterangan</th>
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
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="barcode-box">
                        <img
                            src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($item->sku, 'C128', 2, 50) }}"
                            alt="barcode-{{ $item->sku }}">
                        <div>{{ $item->sku }}</div>
                    </td>
                    <td>{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td class="text-center">{{ $stokSaatIni }}</td>
                    <td>{{ $status }}</td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Belum ada data barang pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
