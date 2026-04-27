<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian PDF</title>
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

        .detail-cell {
            background: #fafafa;
            padding: 10px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .detail-table th,
        .detail-table td {
            font-size: 10px;
            padding: 6px;
        }

        .detail-header {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .detail-total td {
            background: #f1f1f1;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Pembelian</h2>
        <div class="meta">
            Periode: {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
            Dicetak: {{ now()->format('d M Y H:i') }}
        </div>
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
                <tr>
                    <td colspan="7" class="detail-cell">
                        <div class="detail-header">Detail Barang</div>
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Barang</th>
                                    <th>Stok Sekarang</th>
                                    <th>Qty Dibeli</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalDetail = 0;
                                @endphp

                                @forelse ($item->detail as $detail)
                                    @php
                                        $totalDetail += $detail->subtotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $detail->barang->sku ?? '-' }}</td>
                                        <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                        <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td>
                                        <td>{{ $detail->qty }}</td>
                                        <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">Belum ada detail barang pada transaksi ini.</td>
                                    </tr>
                                @endforelse

                                @if ($item->detail->isNotEmpty())
                                    <tr class="detail-total">
                                        <td colspan="5" style="text-align: right;">Total Pembelian</td>
                                        <td>Rp {{ number_format($totalDetail, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </td>
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
