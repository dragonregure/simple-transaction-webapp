<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTransactionRequest;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

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
                'debit' => 0,
                'credit' => 0,
            ]),
            'accounts' => $this->accountOptions(),
            'formAction' => route('transactions.store', [], false),
            'formMethod' => 'POST',
            'submitLabel' => 'Create',
            'title' => 'Create Transaction',
        ]);
    }

    public function store(SaveTransactionRequest $request): Response
    {
        Transaction::query()->create($request->validated());

        return $this->redirectToIndexWithStatus('Transaction created.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.form', [
            'transaction' => $transaction,
            'accounts' => $this->accountOptions(),
            'formAction' => route('transactions.update', $transaction, false),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update',
            'title' => 'Update Transaction',
        ]);
    }

    public function update(SaveTransactionRequest $request, Transaction $transaction): Response
    {
        $transaction->update($request->validated());

        return $this->redirectToIndexWithStatus('Transaction updated.');
    }

    private function redirectToIndexWithStatus(string $message): Response
    {
        session()->flash('status', $message);

        return response('', 302)
            ->header('Location', route('transactions.index', [], false));
    }

    /**
     * @return Collection<int, ChartOfAccount>
     */
    private function accountOptions(): Collection
    {
        return ChartOfAccount::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }
}
