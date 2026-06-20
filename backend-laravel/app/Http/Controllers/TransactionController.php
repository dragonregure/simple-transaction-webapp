<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTransactionRequest;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class TransactionController extends Controller
{
    public function index(): View
    {
        return view('transactions.index', [
            'columns' => [
                ['key' => 'transaction_date', 'name' => 'transaction_date', 'label' => 'Date', 'type' => 'text'],
                [
                    'key' => 'chart_of_account_code',
                    'name' => 'chart_of_accounts.code',
                    'label' => 'Chart of Account Code',
                    'type' => 'text',
                ],
                [
                    'key' => 'chart_of_account_name',
                    'name' => 'chart_of_accounts.name',
                    'label' => 'Chart of Account Name',
                    'type' => 'text',
                ],
                ['key' => 'description', 'name' => 'description', 'label' => 'Description', 'type' => 'text'],
                ['key' => 'debit', 'name' => 'debit', 'label' => 'Debit', 'type' => 'text', 'class' => 'text-end'],
                ['key' => 'credit', 'name' => 'credit', 'label' => 'Credit', 'type' => 'text', 'class' => 'text-end'],
                [
                    'key' => 'actions',
                    'name' => 'actions',
                    'label' => 'Actions',
                    'type' => 'actions',
                    'class' => 'text-end',
                    'orderable' => false,
                    'searchable' => false,
                ],
            ],
            'dataEndpoint' => route('api.v1.transactions.index', [], false),
            'createEndpoint' => route('transactions.create', [], false),
            'perPageOptions' => [10, 25, 50, 100],
            'defaultPerPage' => 10,
        ]);
    }

    public function create(): View
    {
        return view('transactions.form', [
            'transaction' => new Transaction([
                'transaction_date' => now()->toDateString(),
            ]),
            'accountSelectEndpoint' => route('api.v1.chart-of-accounts.select-options', [], false),
            'formAction' => route('transactions.store', [], false),
            'formMethod' => 'POST',
            'idempotencyKey' => (string) Str::uuid(),
            'selectedAccount' => $this->selectedAccountOption(new Transaction()),
            'submitLabel' => 'Create',
            'title' => 'Create Transaction',
        ]);
    }

    public function store(SaveTransactionRequest $request): Response|RedirectResponse
    {
        $attributes = $request->transactionAttributes();

        try {
            DB::transaction(static function () use ($attributes): void {
                Transaction::query()->create($attributes);
            });
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectBackWithSaveError();
        }

        return $this->redirectToIndexWithStatus('Transaction created.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.form', [
            'transaction' => $transaction,
            'accountSelectEndpoint' => route('api.v1.chart-of-accounts.select-options', [], false),
            'formAction' => route('transactions.update', $transaction, false),
            'formMethod' => 'PUT',
            'idempotencyKey' => $transaction->idempotency_key ?: (string) Str::uuid(),
            'selectedAccount' => $this->selectedAccountOption($transaction),
            'submitLabel' => 'Update',
            'title' => 'Update Transaction',
        ]);
    }

    public function update(SaveTransactionRequest $request, Transaction $transaction): Response|RedirectResponse
    {
        $attributes = $request->transactionAttributes();

        try {
            DB::transaction(static function () use ($transaction, $attributes): void {
                $transaction->update($attributes);
            });
        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectBackWithSaveError();
        }

        return $this->redirectToIndexWithStatus('Transaction updated.');
    }

    private function redirectToIndexWithStatus(string $message): Response
    {
        session()->flash('status', $message);

        return response('', 302)
            ->header('Location', route('transactions.index', [], false));
    }

    private function redirectBackWithSaveError(): RedirectResponse
    {
        return back()
            ->withInput()
            ->withErrors([
                'transaction' => 'Unable to save transaction. Please try again.',
            ]);
    }

    private function selectedAccountOption(Transaction $transaction): ?ChartOfAccount
    {
        $selectedAccountId = old('chart_of_account_id', $transaction->chart_of_account_id);

        if (! is_numeric($selectedAccountId)) {
            return null;
        }

        return ChartOfAccount::query()->find((int) $selectedAccountId, ['id', 'code', 'name']);
    }
}
