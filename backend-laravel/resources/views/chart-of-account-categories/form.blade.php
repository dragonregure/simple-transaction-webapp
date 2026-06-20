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
                    <label for="name" class="form-label">Name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $category->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        maxlength="255"
                        required
                        autofocus
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex flex-column flex-sm-row gap-2 justify-content-sm-end">
                <a href="{{ route('chart-of-account-categories.index', [], false) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>{{ $submitLabel }}</span>
                </button>
            </div>
        </form>
    </div>
@endsection
