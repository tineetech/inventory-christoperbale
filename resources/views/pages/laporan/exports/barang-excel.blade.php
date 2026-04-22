<table>
    <thead>
        <tr>
            <th colspan="9">Laporan Barang</th>
        </tr>
        <tr>
            <th colspan="9">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }} |
                Filter Stok: {{ ucfirst($filters['stok']) }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal Input</th>
            <th>SKU</th>
            <th>Nama Barang</th>
            <th>Satuan</th>
            <th>Harga 1</th>
            <th>Harga 2</th>
            <th>Stok</th>
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
                <td>{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                <td>{{ $item->harga_1 ?? 0 }}</td>
                <td>{{ $item->harga_2 ?? 0 }}</td>
                <td>{{ $stokSaatIni }}</td>
                <td>{{ $status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">Belum ada data barang pada filter ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
