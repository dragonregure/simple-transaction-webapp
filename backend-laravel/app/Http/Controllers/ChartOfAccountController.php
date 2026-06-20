<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveChartOfAccountRequest;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class ChartOfAccountController extends Controller
{
    public function index(): View
    {
        return view('chart-of-accounts.index', [
            'columns' => [
                ['key' => 'code', 'name' => 'code', 'label' => 'Code', 'type' => 'text'],
                ['key' => 'name', 'name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['key' => 'category', 'name' => 'category.name', 'label' => 'Category', 'type' => 'text'],
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
            'dataEndpoint' => route('api.v1.chart-of-accounts.index', [], false),
            'createEndpoint' => route('chart-of-accounts.create', [], false),
            'perPageOptions' => [5, 10, 15, 25, 50],
            'defaultPerPage' => 10,
        ]);
    }

    public function create(): View
    {
        return view('chart-of-accounts.form', [
            'account' => new ChartOfAccount(),
            'categories' => $this->categoryOptions(),
            'formAction' => route('chart-of-accounts.store', [], false),
            'formMethod' => 'POST',
            'submitLabel' => 'Create',
            'title' => 'Create Chart of Account',
        ]);
    }

    public function store(SaveChartOfAccountRequest $request): Response
    {
        ChartOfAccount::query()->create($request->validated());

        return $this->redirectToIndexWithStatus('Chart of account created.');
    }

    public function edit(ChartOfAccount $chartOfAccount): View
    {
        return view('chart-of-accounts.form', [
            'account' => $chartOfAccount,
            'categories' => $this->categoryOptions(),
            'formAction' => route('chart-of-accounts.update', $chartOfAccount, false),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update',
            'title' => 'Update Chart of Account',
        ]);
    }

    public function update(SaveChartOfAccountRequest $request, ChartOfAccount $chartOfAccount): Response
    {
        $chartOfAccount->update($request->validated());

        return $this->redirectToIndexWithStatus('Chart of account updated.');
    }

    private function redirectToIndexWithStatus(string $message): Response
    {
        session()->flash('status', $message);

        return response('', 302)
            ->header('Location', route('chart-of-accounts.index', [], false));
    }

    /**
     * @return Collection<int, ChartOfAccountCategory>
     */
    private function categoryOptions(): Collection
    {
        return ChartOfAccountCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
