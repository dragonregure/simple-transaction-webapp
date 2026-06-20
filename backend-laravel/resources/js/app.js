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
        columns: columns.map(dataTableColumn),
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

const dataTableColumn = (column) => ({
    className: column.class || '',
    data: column.key,
    name: column.name || column.key,
    orderable: column.orderable !== false && column.type !== 'actions',
    searchable: column.searchable !== false && column.type !== 'actions',
});

const initialOrder = (columns, initialSort, initialDirection) => {
    const columnIndex = columns.findIndex((column) => column.key === initialSort);

    if (columnIndex === -1) {
        return [];
    }

    return [[columnIndex, initialDirection === 'desc' ? 'desc' : 'asc']];
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
