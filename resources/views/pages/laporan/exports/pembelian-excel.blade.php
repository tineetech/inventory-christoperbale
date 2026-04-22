<table>
    <thead>
        <tr><th colspan="7">Laporan Pembelian</th></tr>
        <tr>
            <th colspan="7">
                Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
            </th>
        </tr>
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
                <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                <td>{{ $item->total_harga }}</td>
                <td>{{ $item->user->nama ?? '-' }}</td>
                <td>{{ $item->keterangan ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada data pembelian pada filter ini.</td></tr>
        @endforelse
    </tbody>
</table>
