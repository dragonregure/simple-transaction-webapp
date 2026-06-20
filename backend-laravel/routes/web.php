<?php

use App\Http\Controllers\ChartOfAccountCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('', 302)
        ->header('Location', route('transactions.index', [], false));
});

Route::view('/transactions', 'transactions.index')
    ->name('transactions.index');

Route::view('/master/chart-of-accounts', 'chart-of-accounts.index')
    ->name('chart-of-accounts.index');

Route::resource('/master/chart-of-account-categories', ChartOfAccountCategoryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->parameters(['chart-of-account-categories' => 'chartOfAccountCategory']);
