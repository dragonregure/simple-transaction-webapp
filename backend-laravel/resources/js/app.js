import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

document.addEventListener('DOMContentLoaded', () => {
    initialiseYajraDataTables();
    initialiseSidebarScrollbar();
});

const initialiseSidebarScrollbar = () => {
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
};

const initialiseYajraDataTables = () => {
    document.querySelectorAll('[data-yajra-data-table]').forEach((dataTable) => {
        if (dataTable.dataset.yajraDataTableReady === 'true') {
            return;
        }

        dataTable.dataset.yajraDataTableReady = 'true';
        bindYajraDataTable(dataTable);
    });
};

const bindYajraDataTable = (dataTable) => {
    const table = dataTable.querySelector('table');
    const endpoint = dataTable.dataset.endpoint;

    if (! table || ! endpoint) {
        return;
    }

    const columns = JSON.parse(dataTable.dataset.columns || '[]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const status = dataTable.querySelector('[data-yajra-table-status]');
    const errorMessage = dataTable.dataset.errorMessage || 'Unable to load records.';
    const rowLabelKey = dataTable.dataset.rowLabelKey || 'name';
    const initialSort = dataTable.dataset.initialSort;
    const initialDirection = dataTable.dataset.initialDirection || 'asc';
    const pageLength = Number(dataTable.dataset.pageLength || 10);
    const lengthMenu = JSON.parse(dataTable.dataset.pageLengthOptions || '[10,25,50]');

    const setStatus = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const tableApi = new DataTable(table, {
        ajax: {
            url: endpoint,
            type: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            error: () => {
                setStatus(errorMessage);
            },
        },
        columns: columns.map((column) => dataTableColumn(column, dataTable, rowLabelKey)),
        lengthMenu,
        order: initialOrder(columns, initialSort, initialDirection),
        pageLength,
        processing: true,
        responsive: true,
        serverSide: true,
    });

    tableApi.on('processing.dt', (_event, _settings, processing) => {
        dataTable.classList.toggle('is-loading', processing);
        setStatus(processing ? 'Loading records' : 'Records loaded');
    });

    table.addEventListener('click', (event) => {
        handleDeleteClick(event, tableApi, dataTable, csrfToken, setStatus);
    });
};

const dataTableColumn = (column, dataTable, rowLabelKey) => ({
    className: column.class || '',
    data: column.type === 'actions' ? null : column.key,
    name: column.name || column.key,
    orderable: column.orderable !== false && column.type !== 'actions',
    searchable: column.searchable !== false && column.type !== 'actions',
    render: (value, type, row) => renderCell(value, type, row, column, dataTable, rowLabelKey),
});

const initialOrder = (columns, initialSort, initialDirection) => {
    const columnIndex = columns.findIndex((column) => column.key === initialSort);

    if (columnIndex === -1) {
        return [];
    }

    return [[columnIndex, initialDirection === 'desc' ? 'desc' : 'asc']];
};

const renderCell = (value, type, row, column, dataTable, rowLabelKey) => {
    if (column.type === 'actions') {
        return renderActions(row, dataTable, rowLabelKey);
    }

    if (type !== 'display') {
        return value ?? '';
    }

    if (column.type === 'datetime') {
        return escapeHtml(formatDate(value));
    }

    return escapeHtml(value);
};

const renderActions = (row, dataTable, rowLabelKey) => {
    const rowLabel = escapeHtml(row[rowLabelKey] || 'record');
    const editEndpoint = escapeHtml(endpointFromTemplate(dataTable.dataset.editEndpointTemplate, row.id));
    const deleteEndpoint = escapeHtml(endpointFromTemplate(dataTable.dataset.deleteEndpointTemplate, row.id));

    return `
        <div class="btn-group btn-group-sm" role="group" aria-label="Actions for ${rowLabel}">
            <a href="${editEndpoint}" class="btn btn-outline-primary" title="Update ${rowLabel}">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <span class="visually-hidden">Update ${rowLabel}</span>
            </a>
            <button
                type="button"
                class="btn btn-outline-danger"
                title="Delete ${rowLabel}"
                data-yajra-table-delete="${deleteEndpoint}"
                data-delete-row-label="${rowLabel}"
            >
                <i class="bi bi-trash" aria-hidden="true"></i>
                <span class="visually-hidden">Delete ${rowLabel}</span>
            </button>
        </div>
    `;
};

const handleDeleteClick = async (event, tableApi, dataTable, csrfToken, setStatus) => {
    const deleteButton = event.target.closest('[data-yajra-table-delete]');

    if (! deleteButton || deleteButton.disabled) {
        return;
    }

    const deleteEndpoint = deleteButton.dataset.yajraTableDelete;
    const rowLabel = deleteButton.dataset.deleteRowLabel || 'this record';

    if (! deleteEndpoint || ! window.confirm(`Delete ${rowLabel}?`)) {
        return;
    }

    deleteButton.disabled = true;
    dataTable.classList.add('is-loading');
    setStatus(`Deleting ${rowLabel}`);

    try {
        const response = await fetch(deleteEndpoint, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (! response.ok) {
            throw new Error(await deleteErrorMessage(response));
        }

        setStatus(`${rowLabel} deleted`);
        tableApi.ajax.reload(null, false);
    } catch (error) {
        window.alert(error.message || 'Unable to delete record.');
        setStatus('Unable to delete record');
    } finally {
        deleteButton.disabled = false;
        dataTable.classList.remove('is-loading');
    }
};

const deleteErrorMessage = async (response) => {
    if (response.headers.get('content-type')?.includes('application/json')) {
        const payload = await response.json();

        return payload.message || 'Unable to delete record.';
    }

    return 'Unable to delete record.';
};

const endpointFromTemplate = (template, id) => {
    if (! template || ! id) {
        return '';
    }

    return template.replace('__ID__', encodeURIComponent(id));
};

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

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
