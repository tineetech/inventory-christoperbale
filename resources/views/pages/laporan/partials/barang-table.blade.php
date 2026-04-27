<div class="table-responsive">
    <table class="table table-modern table-hover mb-0" id="{{ $tableId ?? 'barangTable' }}">
        <thead>
            <tr>
                <th>Barcode</th>
                <th>No</th>
                <th>Tanggal Input</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Harga 1</th>
                <th>Harga 2</th>
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
                    $badgeClass = $stokSaatIni <= 0 ? 'badge-danger' : ($stokSaatIni <= $minimum ? 'badge-warning' : 'badge-success');
                @endphp
                <tr>
                    <td>
                        <img
                            src="data:image/png;base64,{{ \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($item->sku, 'C128', 1.6, 38) }}"
                            alt="barcode-{{ $item->sku }}"
                            style="width: 140px; max-width: 100%;">
                        <div class="small text-muted mt-1">{{ $item->sku }}</div>
                    </td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $item->sku }}</td>
                    <td><strong>{{ $item->nama_barang }}</strong></td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($item->harga_1 ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->harga_2 ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $stokSaatIni }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr data-empty-row="true">
                    <td colspan="11" class="text-center text-muted py-4">Belum ada data barang pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
