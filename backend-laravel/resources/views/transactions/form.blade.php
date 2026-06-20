@extends('layouts.admin')

@section('title', $title)
@section('page-title', $title)

@section('content')
    @php
        $currentAmount = $transaction->exists ? max((int) $transaction->debit, (int) $transaction->credit) : null;
    @endphp

    <div class="card">
        <form action="{{ $formAction }}" method="POST" novalidate>
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">

            <div class="card-body">
                @error('transaction')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror

                <div class="mb-3">
                    <label for="transaction_date" class="form-label">Date</label>
                    <input
                        id="transaction_date"
                        name="transaction_date"
                        type="date"
                        value="{{ old('transaction_date', $transaction->transaction_date?->format('Y-m-d')) }}"
                        class="form-control @error('transaction_date') is-invalid @enderror"
                        required
                        autofocus
                    >
                    @error('transaction_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="chart_of_account_id" class="form-label">Chart of Account</label>
                    <select
                        id="chart_of_account_id"
                        name="chart_of_account_id"
                        class="form-select @error('chart_of_account_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Select chart of account</option>
                        @foreach ($accounts as $account)
                            <option
                                value="{{ $account->id }}"
                                @selected((string) old('chart_of_account_id', $transaction->chart_of_account_id) === (string) $account->id)
                            >
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('chart_of_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="3"
                        maxlength="1000"
                    >{{ old('description', $transaction->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input
                        id="amount"
                        name="amount"
                        type="number"
                        value="{{ old('amount', $currentAmount) }}"
                        class="form-control @error('amount') is-invalid @enderror"
                        min="1"
                        step="1"
                        required
                    >
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex flex-column flex-sm-row gap-2 justify-content-sm-end">
                <a href="{{ route('transactions.index', [], false) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>{{ $submitLabel }}</span>
                </button>
            </div>
        </form>
    </div>
@endsection
