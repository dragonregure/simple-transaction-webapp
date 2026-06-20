<?php

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\ChartOfAccountCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('', 302)
        ->header('Location', route('transactions.index', [], false));
});

Route::view('/transactions', 'transactions.index')
    ->name('transactions.index');

Route::resource('/master/chart-of-accounts', ChartOfAccountController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->parameters(['chart-of-accounts' => 'chartOfAccount']);

Route::resource('/master/chart-of-account-categories', ChartOfAccountCategoryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->parameters(['chart-of-account-categories' => 'chartOfAccountCategory']);
