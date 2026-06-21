<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Transaction $transaction */
        $transaction = $this->resource;
        $account = $transaction->chartOfAccount;
        $transactionDate = $transaction->transaction_date;

        return [
            'id' => $transaction->id,
            'idempotency_key' => $transaction->idempotency_key,
            'transaction_date' => $transactionDate instanceof DateTimeInterface
                ? $transactionDate->format('Y-m-d')
                : $transactionDate,
            'chart_of_account_id' => $transaction->chart_of_account_id,
            'chart_of_account' => $account === null ? null : [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'text' => sprintf('%s - %s', (string) $account->code, (string) $account->name),
            ],
            'description' => $transaction->description,
            'amount' => max((int) $transaction->debit, (int) $transaction->credit),
            'debit' => $transaction->debit,
            'credit' => $transaction->credit,
        ];
    }
}
