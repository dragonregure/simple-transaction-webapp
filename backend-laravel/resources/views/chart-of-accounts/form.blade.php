@extends('layouts.admin')

@section('title', $title)
@section('page-title', $title)

@section('content')
    <div class="card">
        <form action="{{ $formAction }}" method="POST" novalidate>
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="card-body">
                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        value="{{ old('code', $account->code) }}"
                        class="form-control @error('code') is-invalid @enderror"
                        maxlength="255"
                        required
                        autofocus
                    >
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $account->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        maxlength="255"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select
                        id="category_id"
                        name="category_id"
                        class="form-select @error('category_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) old('category_id', $account->category_id) === (string) $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="account_type" class="form-label">Type</label>
                    <select
                        id="account_type"
                        name="account_type"
                        class="form-select @error('account_type') is-invalid @enderror"
                        required
                    >
                        <option value="">Select type</option>
                        @foreach ($accountTypes as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('account_type', $account->account_type) === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex flex-column flex-sm-row gap-2 justify-content-sm-end">
                <a href="{{ route('chart-of-accounts.index', [], false) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>{{ $submitLabel }}</span>
                </button>
            </div>
        </form>
    </div>
@endsection
