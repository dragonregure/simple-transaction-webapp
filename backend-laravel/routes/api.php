<?php

use App\Http\Controllers\Api\V1\ChartOfAccountController;
use App\Http\Controllers\Api\V1\ChartOfAccountCategoryController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');

    Route::get('/chart-of-accounts', [ChartOfAccountController::class, 'index'])
        ->name('chart-of-accounts.index');
    Route::get('/chart-of-accounts/select-options', [ChartOfAccountController::class, 'selectOptions'])
        ->name('chart-of-accounts.select-options');
    Route::delete('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'destroy'])
        ->name('chart-of-accounts.destroy');

    Route::get('/chart-of-account-categories', [ChartOfAccountCategoryController::class, 'index'])
        ->name('chart-of-account-categories.index');
    Route::get(
        '/chart-of-account-categories/select-options',
        [ChartOfAccountCategoryController::class, 'selectOptions']
    )->name('chart-of-account-categories.select-options');
    Route::delete('/chart-of-account-categories/{chartOfAccountCategory}', [ChartOfAccountCategoryController::class, 'destroy'])
        ->name('chart-of-account-categories.destroy');
});
