<table>
    <thead>
        <tr><th colspan="7">Laporan Stok</th></tr>
        <tr>
            <th colspan="7">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
            </th>
        </tr>
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
            <tr><td colspan="7">Belum ada data stok pada filter ini.</td></tr>
        @endforelse
    </tbody>
</table>
