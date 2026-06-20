<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ChartOfAccountCategoryController extends Controller
{
    public function index(): View
    {
        return view('chart-of-account-categories.index', [
            'columns' => [
                ['key' => 'name', 'type' => 'text'],
                ['key' => 'created_at', 'type' => 'datetime'],
                ['key' => 'actions', 'type' => 'actions', 'class' => 'text-end'],
            ],
            'dataEndpoint' => route('api.v1.chart-of-account-categories.index', [], false),
            'perPageOptions' => [5, 10, 15, 25, 50],
        ]);
    }
}
