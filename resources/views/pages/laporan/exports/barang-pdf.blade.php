<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Barang PDF</title>
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

        .barcode img {
            width: 170px;
            height: 44px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Barang</h2>
        <div class="meta">
            Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
            Filter Stok: {{ ucfirst($filters['stok']) }} |
            Dicetak: {{ now()->format('d M Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="18%">Barcode</th>
                <th width="10%">Tanggal</th>
                <th width="10%">SKU</th>
                <th width="16%">Nama Barang</th>
                <th width="8%">Satuan</th>
                <th width="9%">Harga 1</th>
                <th width="9%">Harga 2</th>
                <th width="6%">Stok</th>
                <th width="8%">Status</th>
                <th width="12%">Keterangan</th>
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
                    <td class="text-center">
                        <img
                            src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($item->sku, 'C128', 2, 50) }}"
                            alt="barcode-{{ $item->sku }}">
                        <div>{{ $item->sku }}</div>
                    </td>
                    <td>{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($item->harga_1 ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->harga_2 ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $stokSaatIni }}</td>
                    <td>{{ $status }}</td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Belum ada data barang pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
