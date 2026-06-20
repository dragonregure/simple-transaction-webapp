@php
    $rowLabel = $account->name ?: 'record';
@endphp

<div class="btn-group btn-group-sm" role="group" aria-label="Actions for {{ $rowLabel }}">
    <a href="{{ route('chart-of-accounts.edit', $account, false) }}" class="btn btn-outline-primary" title="Update {{ $rowLabel }}">
        <i class="bi bi-pencil-square" aria-hidden="true"></i>
        <span class="visually-hidden">Update {{ $rowLabel }}</span>
    </a>
    <button
        type="button"
        class="btn btn-outline-danger"
        title="Delete {{ $rowLabel }}"
        data-yajra-table-delete="{{ route('api.v1.chart-of-accounts.destroy', $account, false) }}"
        data-delete-row-label="{{ $rowLabel }}"
    >
        <i class="bi bi-trash" aria-hidden="true"></i>
        <span class="visually-hidden">Delete {{ $rowLabel }}</span>
    </button>
</div>
