<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'debit' => $this->normalizeAmount($this->input('debit')),
            'credit' => $this->normalizeAmount($this->input('credit')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $transaction = $this->route('transaction');

        return [
            'idempotency_key' => [
                'required',
                'uuid',
                Rule::unique('transactions', 'idempotency_key')
                    ->ignore($transaction instanceof Transaction ? $transaction->id : null),
            ],
            'transaction_date' => ['required', 'date'],
            'chart_of_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'debit' => ['required', 'integer', 'min:0'],
            'credit' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $debit = (int) $this->input('debit', 0);
            $credit = (int) $this->input('credit', 0);

            if (($debit > 0 && $credit > 0) || ($debit === 0 && $credit === 0)) {
                $validator->errors()->add(
                    'debit',
                    'Enter either a debit amount or a credit amount, but not both.'
                );
            }
        });
    }

    private function normalizeAmount(mixed $amount): mixed
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        return $amount;
    }
}
