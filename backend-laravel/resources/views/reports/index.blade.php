@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')

@php
    $rowStyles = [
        'income' => '--bs-table-bg: #c5e0b3;',
        'total-income' => '--bs-table-bg: #a8d08d;',
        'expense' => '--bs-table-bg: #f7caac;',
        'total-expense' => '--bs-table-bg: #f4b083;',
    ];
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap">
                <form action="{{ route('reports.index', [], false) }}" method="GET" class="d-flex align-items-end gap-2">
                    <div>
                        <label for="report-year" class="form-label">Year</label>
                        <select id="report-year" name="year" class="form-select">
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" @selected($year === $report['year'])>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary">
                        Apply
                    </button>
                </form>

                <a
                    href="{{ route('reports.export', ['year' => $report['year']], false) }}"
                    class="btn btn-primary"
                    title="Export report"
                >
                    <i class="bi bi-file-earmark-excel" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th
                                scope="col"
                                rowspan="2"
                                class="text-center align-middle"
                                style="--bs-table-bg: #ffff00;"
                            >
                                Category
                            </th>
                            @foreach ($report['months'] as $month)
                                <th scope="col" class="text-center" style="--bs-table-bg: #ffff00;">
                                    {{ $month }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($report['months'] as $month)
                                <th scope="col" class="text-center" style="--bs-table-bg: #ffff00;">
                                    Amount
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['income_rows'] as $row)
                            @include('reports.partials.amount-row', [
                                'label' => $row['label'],
                                'amounts' => $row['amounts'],
                                'style' => $rowStyles['income'],
                            ])
                        @endforeach

                        @include('reports.partials.amount-row', [
                            'label' => 'Total Income',
                            'amounts' => $report['total_income'],
                            'style' => $rowStyles['total-income'],
                        ])

                        @foreach ($report['expense_rows'] as $row)
                            @include('reports.partials.amount-row', [
                                'label' => $row['label'],
                                'amounts' => $row['amounts'],
                                'style' => $rowStyles['expense'],
                            ])
                        @endforeach

                        @include('reports.partials.amount-row', [
                            'label' => 'Total Expense',
                            'amounts' => $report['total_expense'],
                            'style' => $rowStyles['total-expense'],
                        ])

                        @include('reports.partials.amount-row', [
                            'label' => 'Net Income',
                            'amounts' => $report['net_income'],
                            'style' => null,
                        ])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
