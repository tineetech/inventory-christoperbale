<div class="table-responsive">
    <table class="table table-modern table-hover mb-0" id="{{ $tableId ?? 'stokKritisTable' }}">
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
                    $badgeClass = $stokSaatIni <= 0
                        ? 'badge-danger'
                        : ($stokSaatIni < 10 && $stokSaatIni > $minimum
                            ? 'badge-info'
                            : 'badge-warning');
                @endphp
                <tr>
                    <td>{{ $stokKritis->firstItem() + $index }}</td>
                    <td><code>{{ $item->sku }}</code></td>
                    <td><strong>{{ $item->nama_barang }}</strong></td>
                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                    <td>{{ $minimum }}</td>
                    <td>
                        <span class="text-danger font-weight-bold">{{ $stokSaatIni }}</span>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
                </tr>
            @empty
                <tr data-empty-row="true">
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="feather icon-check-circle text-success mr-2"></i>
                        Semua stok aman
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>