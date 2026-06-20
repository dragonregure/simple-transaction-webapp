<?php

namespace App\Http\Requests;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => $this->normalizeAmount($this->input('amount')),
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
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function transactionAttributes(): array
    {
        $validated = $this->validated();
        $account = ChartOfAccount::query()->findOrFail($validated['chart_of_account_id']);
        $amount = (int) $validated['amount'];

        unset($validated['amount']);

        return array_merge($validated, $account->transactionAmountsFor($amount));
    }

    private function normalizeAmount(mixed $amount): mixed
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        return $amount;
    }
}
