<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Models\Transaction;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function dataTable(): EloquentDataTable
    {
        return DataTables::eloquent($this->tableQuery())
            ->only([
                'id',
                'transaction_date',
                'chart_of_account_code',
                'chart_of_account_name',
                'description',
                'debit',
                'credit',
                'actions',
            ])
            ->whitelist([
                'id',
                'transaction_date',
                'chart_of_accounts.code',
                'chart_of_accounts.name',
                'description',
                'debit',
                'credit',
            ])
            ->editColumn(
                'transaction_date',
                fn (Transaction $transaction): string => $this->formatTransactionDate($transaction)
            )
            ->editColumn(
                'debit',
                fn (Transaction $transaction): string => number_format($transaction->debit)
            )
            ->editColumn(
                'credit',
                fn (Transaction $transaction): string => number_format($transaction->credit)
            )
            ->addColumn(
                'actions',
                fn (Transaction $transaction): string => view(
                    'transactions.partials.table-actions',
                    ['transaction' => $transaction]
                )->render()
            )
            ->rawColumns(['actions']);
    }

    /**
     * @return Builder<Transaction>
     */
    private function tableQuery(): Builder
    {
        return Transaction::query()
            ->leftJoin(
                'chart_of_accounts',
                'transactions.chart_of_account_id',
                '=',
                'chart_of_accounts.id'
            )
            ->select([
                'transactions.id',
                'transactions.chart_of_account_id',
                'transactions.transaction_date',
                'transactions.description',
                'transactions.debit',
                'transactions.credit',
                'chart_of_accounts.code as chart_of_account_code',
                'chart_of_accounts.name as chart_of_account_name',
            ]);
    }

    private function formatTransactionDate(Transaction $transaction): string
    {
        $transactionDate = $transaction->getAttribute('transaction_date');

        if ($transactionDate instanceof DateTimeInterface) {
            return $transactionDate->format('Y-m-d');
        }

        if (is_string($transactionDate) && $transactionDate !== '') {
            return $transactionDate;
        }

        return '-';
    }
}
