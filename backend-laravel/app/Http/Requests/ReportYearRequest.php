<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportYearRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'between:1900,2100'],
        ];
    }

    public function selectedYear(): ?int
    {
        if (! $this->filled('year')) {
            return null;
        }

        return (int) $this->validated('year');
    }
}
