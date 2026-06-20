<?php

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\ChartOfAccountCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('', 302)
        ->header('Location', route('transactions.index', [], false));
});

Route::resource('/transactions', TransactionController::class)
    ->only(['index', 'create', 'store', 'edit', 'update']);

Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index');
Route::get('/reports/export', [ReportController::class, 'export'])
    ->name('reports.export');

Route::resource('/master/chart-of-accounts', ChartOfAccountController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->parameters(['chart-of-accounts' => 'chartOfAccount']);

Route::resource('/master/chart-of-account-categories', ChartOfAccountCategoryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->parameters(['chart-of-account-categories' => 'chartOfAccountCategory']);
