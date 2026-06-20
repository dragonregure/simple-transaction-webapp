@extends('layouts.admin')

@section('title', 'Master Chart of Account Categories')
@section('page-title', 'Master Chart of Account Categories')

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div
        class="card"
        data-yajra-data-table
        data-endpoint="{{ $dataEndpoint }}"
        data-initial-sort="name"
        data-initial-direction="asc"
        data-page-length="{{ $defaultPerPage }}"
        data-page-length-options='@json($perPageOptions)'
        data-columns='@json($columns)'
        data-error-message="Unable to load chart of account categories."
    >
        <div class="card-header">
            <div class="d-flex justify-content-end">
                <a href="{{ $createEndpoint }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th scope="col" @class(['text-end' => ($column['class'] ?? '') === 'text-end'])>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="visually-hidden" aria-live="polite" data-yajra-table-status></div>
    </div>
@endsection
