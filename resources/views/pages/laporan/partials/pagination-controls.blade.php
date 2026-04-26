<div class="d-flex justify-content-between align-items-center flex-wrap px-3 py-2 border-top" style="gap: 12px;">
    <div class="d-flex align-items-center">
        <span class="mr-2 text-muted small">Show</span>
        <select
            class="form-control form-control-sm"
            id="{{ $prefix }}EntriesSelect"
            name="per_page"
            form="{{ $formId ?? '' }}"
            style="width: 80px;">
            @foreach ([10, 25, 50, 100] as $option)
                <option value="{{ $option }}" {{ (int) $perPage === $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
        <span class="ml-2 text-muted small">entries</span>
    </div>

    <div class="text-muted small" id="{{ $prefix }}TableInfo">
        Showing 0 to 0 of {{ $totalRows }} entries
    </div>

    <nav>
        <ul class="pagination pagination-sm mb-0" id="{{ $prefix }}Pagination"></ul>
    </nav>
</div>
