<script>
    if (typeof window.initReportPagination !== 'function') {
        window.initReportPagination = function(config) {
            const tables = (config.tableIds || [])
                .map((tableId) => document.getElementById(tableId))
                .filter(Boolean);

            if (!tables.length) {
                return;
            }

            const entriesSelect = document.getElementById(config.entriesSelectId);
            const pagination = document.getElementById(config.paginationId);
            const tableInfo = document.getElementById(config.tableInfoId);
            const filterForm = config.formId ? document.getElementById(config.formId) : null;
            const perPageInput = filterForm && config.perPageInputName
                ? filterForm.querySelector(`[name="${config.perPageInputName}"]`)
                : null;
            const emptyRowSelector = 'tr[data-empty-row="true"]';
            const mainRowSelector = config.mainRowSelector || 'tbody tr[data-report-main]';
            const detailRowSelector = config.detailRowSelector || 'tbody tr[data-report-detail]';

            if (!entriesSelect || !pagination || !tableInfo) {
                return;
            }

            let currentPage = 1;
            let rowsPerPage = parseInt(entriesSelect.value, 10) || 10;

            tables.forEach((table) => {
                if (table.dataset.reportToggleBound === 'true') {
                    return;
                }

                table.addEventListener('click', function(event) {
                    const mainRow = event.target.closest(mainRowSelector);

                    if (!mainRow) {
                        return;
                    }

                    if (event.target.closest('a, button, input, select, textarea, label')) {
                        return;
                    }

                    const rowId = mainRow.getAttribute('data-id');
                    const detailRow = rowId ? document.getElementById(`detail-${rowId}`) : null;

                    if (!detailRow || !detailRow.matches(detailRowSelector)) {
                        return;
                    }

                    const shouldOpen = mainRow.dataset.expanded !== 'true';

                    mainRow.dataset.expanded = shouldOpen ? 'true' : 'false';
                    detailRow.style.display = shouldOpen ? 'table-row' : 'none';
                });

                table.dataset.reportToggleBound = 'true';
            });

            const dataRowsPerTable = tables.map((table) => {
                const mainRows = Array.from(table.querySelectorAll(mainRowSelector));

                if (mainRows.length) {
                    return mainRows;
                }

                return Array.from(table.querySelectorAll('tbody tr')).filter((row) => !row.matches(emptyRowSelector));
            });
            const emptyRows = tables.map((table) => table.querySelector(emptyRowSelector));
            const totalRows = dataRowsPerTable.reduce((maxRows, rows) => Math.max(maxRows, rows.length), 0);

            function updateInfo() {
                if (totalRows === 0) {
                    tableInfo.textContent = 'Showing 0 to 0 of 0 entries';
                    return;
                }

                const start = (currentPage - 1) * rowsPerPage + 1;
                const end = Math.min(currentPage * rowsPerPage, totalRows);

                tableInfo.textContent = `Showing ${start} to ${end} of ${totalRows} entries`;
            }

            function renderPagination() {
                pagination.innerHTML = '';

                const pageCount = Math.ceil(totalRows / rowsPerPage);

                if (pageCount <= 1) {
                    return;
                }

                for (let page = 1; page <= pageCount; page += 1) {
                    const item = document.createElement('li');
                    item.className = `page-item${page === currentPage ? ' active' : ''}`;

                    const link = document.createElement('a');
                    link.className = 'page-link';
                    link.href = '#';
                    link.textContent = page;
                    link.addEventListener('click', function(event) {
                        event.preventDefault();
                        currentPage = page;
                        displayTable();
                    });

                    item.appendChild(link);
                    pagination.appendChild(item);
                }
            }

            function displayTable() {
                rowsPerPage = parseInt(entriesSelect.value, 10) || 10;

                const pageCount = Math.max(1, Math.ceil(Math.max(totalRows, 1) / rowsPerPage));
                currentPage = Math.min(currentPage, pageCount);

                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                dataRowsPerTable.forEach((rows, index) => {
                    rows.forEach((row) => {
                        row.style.display = 'none';

                        const rowId = row.getAttribute('data-id');
                        const detailRow = rowId ? document.getElementById(`detail-${rowId}`) : null;

                        if (detailRow && detailRow.matches(detailRowSelector)) {
                            detailRow.style.display = 'none';
                        }
                    });

                    rows.slice(start, end).forEach((row) => {
                        row.style.display = '';

                        const rowId = row.getAttribute('data-id');
                        const detailRow = rowId ? document.getElementById(`detail-${rowId}`) : null;

                        if (
                            detailRow &&
                            detailRow.matches(detailRowSelector) &&
                            row.dataset.expanded === 'true'
                        ) {
                            detailRow.style.display = 'table-row';
                        }
                    });

                    if (emptyRows[index]) {
                        emptyRows[index].style.display = rows.length === 0 ? '' : 'none';
                    }
                });

                updateInfo();
                renderPagination();
            }

            entriesSelect.addEventListener('change', function() {
                currentPage = 1;

                if (perPageInput) {
                    perPageInput.value = entriesSelect.value;
                }

                displayTable();
            });

            displayTable();
        };
    }
</script>
