<table>
    <thead>
        <tr>
            <th colspan="7">Laporan Stok Kritis</th>
        </tr>
        <tr>
            <th colspan="7">
                Stok dibawah minimum dan dibawah 10 yang perlu di restock |
                Filter Status: {{ $filters['status'] ?? 'semua' }}
            </th>
        </tr>
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
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                <td>{{ $minimum }}</td>
                <td>{{ $stokSaatIni }}</td>
                <td>{{ $status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Semua stok aman.</td>
            </tr>
        @endforelse
    </tbody>
</table>