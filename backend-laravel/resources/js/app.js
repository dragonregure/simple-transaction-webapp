document.addEventListener('DOMContentLoaded', () => {
    initialiseAdminLteDataTables();

    const sidebarWrapper = document.querySelector('.sidebar-wrapper');
    const overlayScrollbars = window.OverlayScrollbarsGlobal?.OverlayScrollbars;
    const shouldUseCustomScrollbar = sidebarWrapper && overlayScrollbars && window.innerWidth > 992;

    if (! shouldUseCustomScrollbar) {
        return;
    }

    overlayScrollbars(sidebarWrapper, {
        scrollbars: {
            theme: 'os-theme-light',
            autoHide: 'leave',
            clickScroll: true,
        },
    });
});

const initialiseAdminLteDataTables = () => {
    document.querySelectorAll('[data-adminlte-data-table]').forEach((dataTable) => {
        if (dataTable.dataset.adminlteDataTableReady === 'true') {
            return;
        }

        dataTable.dataset.adminlteDataTableReady = 'true';
        bindAdminLteDataTable(dataTable);
    });
};

const bindAdminLteDataTable = (dataTable) => {
    const endpoint = dataTable.dataset.endpoint;
    const searchInput = dataTable.querySelector('[data-adminlte-table-search]');
    const perPageSelect = dataTable.querySelector('[data-adminlte-table-per-page]');
    const filterFields = dataTable.querySelectorAll('[data-adminlte-table-filter]');
    const sortButtons = dataTable.querySelectorAll('[data-adminlte-table-sort]');
    const tableBody = dataTable.querySelector('[data-adminlte-table-body]');
    const pagination = dataTable.querySelector('[data-adminlte-table-pagination]');
    const summary = dataTable.querySelector('[data-adminlte-table-summary]');
    const status = dataTable.querySelector('[data-adminlte-table-status]');
    let debounceTimer = null;
    let activeRequest = null;

    if (! endpoint || ! tableBody) {
        return;
    }

    const columns = JSON.parse(dataTable.dataset.columns || '[]');
    const rowLabelKey = dataTable.dataset.rowLabelKey || 'name';
    const emptyMessage = dataTable.dataset.emptyMessage || 'No records found.';
    const errorMessage = dataTable.dataset.errorMessage || 'Unable to load records.';
    const browserParams = new URLSearchParams(window.location.search);
    const state = {
        page: Number(browserParams.get('page') || 1),
        perPage: Number(browserParams.get('per_page') || perPageSelect?.value || 10),
        search: browserParams.get('search') || '',
        sort: browserParams.get('sort') || dataTable.dataset.initialSort || 'name',
        direction: browserParams.get('direction') || dataTable.dataset.initialDirection || 'asc',
        filters: {},
    };

    filterFields.forEach((field) => {
        const filterName = field.dataset.adminlteTableFilter;
        const browserValue = filterName ? browserParams.get(`filter[${filterName}]`) : null;

        if (filterName) {
            state.filters[filterName] = browserValue || field.value || '';
            field.value = state.filters[filterName];
        }
    });

    if (searchInput) {
        searchInput.value = state.search;
    }

    if (perPageSelect) {
        perPageSelect.value = String(state.perPage);
    }

    const setStatus = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const formatDate = (value) => {
        if (! value) {
            return '-';
        }

        return new Intl.DateTimeFormat(undefined, {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(value));
    };

    const queryString = () => {
        const query = new URLSearchParams();
        query.set('page', String(state.page));
        query.set('per_page', String(state.perPage));

        if (state.search.trim()) {
            query.set('search', state.search.trim());
        }

        if (state.sort) {
            query.set('sort', state.sort);
            query.set('direction', state.direction);
        }

        Object.entries(state.filters).forEach(([key, value]) => {
            if (value && value !== 'all') {
                query.set(`filter[${key}]`, value);
            }
        });

        return query.toString();
    };

    const syncBrowserUrl = () => {
        const query = queryString();
        const nextUrl = query ? `${window.location.pathname}?${query}` : window.location.pathname;
        window.history.replaceState({}, '', nextUrl);
    };

    const renderRows = (rows) => {
        if (rows.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${columns.length || 1}" class="py-4 text-center text-body-secondary">
                        ${escapeHtml(emptyMessage)}
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = rows.map((category) => `
            <tr>
                ${columns.map((column) => renderCell(category, column)).join('')}
            </tr>
        `).join('');
    };

    const renderCell = (row, column) => {
        const className = column.class ? ` class="${escapeHtml(column.class)}"` : '';
        const value = row[column.key];

        if (column.type === 'actions') {
            const rowLabel = escapeHtml(row[rowLabelKey] || 'record');

            return `
                <td${className}>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Actions for ${rowLabel}">
                        <button type="button" class="btn btn-outline-primary" title="Update ${rowLabel}">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                            <span class="visually-hidden">Update ${rowLabel}</span>
                        </button>
                        <button type="button" class="btn btn-outline-danger" title="Delete ${rowLabel}">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                            <span class="visually-hidden">Delete ${rowLabel}</span>
                        </button>
                    </div>
                </td>
            `;
        }

        if (column.type === 'datetime') {
            return `<td${className}>${escapeHtml(formatDate(value))}</td>`;
        }

        return `<td${className}>${escapeHtml(value)}</td>`;
    };

    const renderSummary = (meta) => {
        if (! summary) {
            return;
        }

        if (! meta || meta.total === 0) {
            summary.textContent = 'Showing 0 results';
            return;
        }

        summary.textContent = `Showing ${meta.from} to ${meta.to} of ${meta.total} results`;
    };

    const pageItem = (label, page, disabled = false, active = false) => `
        <li class="page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}">
            <button type="button" class="page-link" data-adminlte-table-page="${page}" ${disabled ? 'disabled' : ''}>
                ${label}
            </button>
        </li>
    `;

    const renderPagination = (meta) => {
        if (! pagination || ! meta) {
            return;
        }

        const currentPage = meta.current_page;
        const lastPage = meta.last_page;
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(lastPage, currentPage + 2);
        const items = [
            pageItem('&laquo;', Math.max(1, currentPage - 1), currentPage === 1),
        ];

        for (let page = startPage; page <= endPage; page += 1) {
            items.push(pageItem(String(page), page, false, page === currentPage));
        }

        items.push(pageItem('&raquo;', Math.min(lastPage, currentPage + 1), currentPage === lastPage));
        pagination.innerHTML = items.join('');
    };

    const renderSortIcons = () => {
        sortButtons.forEach((button) => {
            const sort = button.dataset.adminlteTableSort;
            const icon = dataTable.querySelector(`[data-adminlte-table-sort-icon="${sort}"]`);
            const tableHeader = button.closest('th');
            if (! icon) {
                return;
            }

            icon.className = sort === state.sort
                ? `bi bi-caret-${state.direction === 'asc' ? 'up' : 'down'}-fill`
                : 'bi bi-arrow-down-up';

            if (tableHeader) {
                if (sort === state.sort) {
                    tableHeader.setAttribute('aria-sort', state.direction === 'asc' ? 'ascending' : 'descending');
                } else {
                    tableHeader.removeAttribute('aria-sort');
                }
            }
        });
    };

    const loadTable = async () => {
        window.clearTimeout(debounceTimer);
        activeRequest?.abort();

        const requestController = new AbortController();
        const targetUrl = `${endpoint}?${queryString()}`;
        activeRequest = requestController;
        dataTable.classList.add('is-loading');
        setStatus('Loading categories');
        renderSortIcons();

        try {
            const response = await fetch(targetUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (! response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const payload = await response.json();
            renderRows(payload.data || []);
            renderSummary(payload.meta);
            renderPagination(payload.meta);
            syncBrowserUrl();
            setStatus('Categories loaded');
        } catch (error) {
            if (error.name !== 'AbortError') {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="${columns.length || 1}" class="py-4 text-center text-danger">
                            ${escapeHtml(errorMessage)}
                        </td>
                    </tr>
                `;
                setStatus('Unable to load categories');
            }
        } finally {
            if (activeRequest === requestController) {
                activeRequest = null;
                dataTable.classList.remove('is-loading');
            }
        }
    };

    const scheduleSearch = () => {
        window.clearTimeout(debounceTimer);
        activeRequest?.abort();
        state.page = 1;
        state.search = searchInput?.value || '';
        debounceTimer = window.setTimeout(() => loadTable(), 1000);
    };

    searchInput?.addEventListener('input', scheduleSearch);

    perPageSelect?.addEventListener('change', () => {
        state.page = 1;
        state.perPage = Number(perPageSelect.value || 10);
        loadTable();
    });

    filterFields.forEach((field) => {
        field.addEventListener('change', () => {
            const filterName = field.dataset.adminlteTableFilter;
            if (filterName) {
                state.filters[filterName] = field.value;
            }

            state.page = 1;
            loadTable();
        });
    });

    sortButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const sort = button.dataset.adminlteTableSort;
            if (! sort) {
                return;
            }

            state.page = 1;
            state.direction = state.sort === sort && state.direction === 'asc' ? 'desc' : 'asc';
            state.sort = sort;
            loadTable();
        });
    });

    pagination?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-adminlte-table-page]');

        if (! button || button.disabled) {
            return;
        }

        state.page = Number(button.dataset.adminlteTablePage || 1);
        loadTable();
    });

    loadTable();
};
