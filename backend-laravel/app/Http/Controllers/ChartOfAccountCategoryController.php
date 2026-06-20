<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveChartOfAccountCategoryRequest;
use App\Models\ChartOfAccountCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ChartOfAccountCategoryController extends Controller
{
    public function index(): View
    {
        return view('chart-of-account-categories.index', [
            'columns' => [
                ['key' => 'name', 'name' => 'name', 'label' => 'Name', 'type' => 'text'],
                [
                    'key' => 'created_at',
                    'name' => 'created_at',
                    'label' => 'Created At',
                    'type' => 'datetime',
                    'searchable' => false,
                ],
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
            'dataEndpoint' => route('api.v1.chart-of-account-categories.index', [], false),
            'createEndpoint' => route('chart-of-account-categories.create', [], false),
            'editEndpointTemplate' => route(
                'chart-of-account-categories.edit',
                ['chartOfAccountCategory' => '__ID__'],
                false
            ),
            'deleteEndpointTemplate' => route(
                'api.v1.chart-of-account-categories.destroy',
                ['chartOfAccountCategory' => '__ID__'],
                false
            ),
            'perPageOptions' => [5, 10, 15, 25, 50],
            'defaultPerPage' => 10,
        ]);
    }

    public function create(): View
    {
        return view('chart-of-account-categories.form', [
            'category' => new ChartOfAccountCategory(),
            'formAction' => route('chart-of-account-categories.store', [], false),
            'formMethod' => 'POST',
            'submitLabel' => 'Create',
            'title' => 'Create Chart of Account Category',
        ]);
    }

    public function store(SaveChartOfAccountCategoryRequest $request): Response
    {
        ChartOfAccountCategory::query()->create($request->validated());

        return $this->redirectToIndexWithStatus('Chart of account category created.');
    }

    public function edit(ChartOfAccountCategory $chartOfAccountCategory): View
    {
        return view('chart-of-account-categories.form', [
            'category' => $chartOfAccountCategory,
            'formAction' => route('chart-of-account-categories.update', $chartOfAccountCategory, false),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update',
            'title' => 'Update Chart of Account Category',
        ]);
    }

    public function update(
        SaveChartOfAccountCategoryRequest $request,
        ChartOfAccountCategory $chartOfAccountCategory
    ): Response {
        $chartOfAccountCategory->update($request->validated());

        return $this->redirectToIndexWithStatus('Chart of account category updated.');
    }

    private function redirectToIndexWithStatus(string $message): Response
    {
        session()->flash('status', $message);

        return response('', 302)
            ->header('Location', route('chart-of-account-categories.index', [], false));
    }
}
