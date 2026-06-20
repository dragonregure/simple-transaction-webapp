@extends('layouts.admin')

@section('title', 'Chart of Account Categories')
@section('page-title', 'Chart of Account Categories')

@section('content')
    <div
        class="card"
        data-adminlte-data-table
        data-endpoint="{{ $dataEndpoint }}"
        data-initial-sort="name"
        data-initial-direction="asc"
        data-columns='@json($columns)'
        data-row-label-key="name"
        data-empty-message="No chart of account categories found."
        data-error-message="Unable to load chart of account categories."
    >
        <div class="card-header">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg">
                    <label for="category-search" class="form-label">Search categories</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            id="category-search"
                            type="search"
                            class="form-control"
                            placeholder="Search by category name"
                            autocomplete="off"
                            data-adminlte-table-search
                        >
                    </div>
                </div>

                <div class="col-12 col-md-auto">
                    <label for="category-per-page" class="form-label">Rows</label>
                    <select id="category-per-page" class="form-select" data-adminlte-table-per-page>
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($option === 10)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">
                                <button type="button" class="btn btn-link p-0 data-table-sort-link" data-adminlte-table-sort="name">
                                    <span>Name</span>
                                    <i class="bi bi-caret-up-fill" aria-hidden="true" data-adminlte-table-sort-icon="name"></i>
                                </button>
                            </th>
                            <th scope="col">
                                <button type="button" class="btn btn-link p-0 data-table-sort-link" data-adminlte-table-sort="created_at">
                                    <span>Created At</span>
                                    <i class="bi bi-arrow-down-up" aria-hidden="true" data-adminlte-table-sort-icon="created_at"></i>
                                </button>
                            </th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody data-adminlte-table-body>
                        <tr>
                            <td colspan="3" class="py-4 text-center text-body-secondary">Loading categories...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-md-between">
            <p class="mb-0 text-body-secondary" data-adminlte-table-summary>Loading categories...</p>
            <nav aria-label="Chart of account category pages">
                <ul class="pagination pagination-sm mb-0" data-adminlte-table-pagination></ul>
            </nav>
        </div>

        <div class="visually-hidden" aria-live="polite" data-adminlte-table-status></div>
    </div>
@endsection
