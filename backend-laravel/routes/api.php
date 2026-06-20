<?php

use App\Http\Controllers\Api\V1\ChartOfAccountController;
use App\Http\Controllers\Api\V1\ChartOfAccountCategoryController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])
        ->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])
        ->name('reports.export');

    Route::get('/chart-of-accounts', [ChartOfAccountController::class, 'index'])
        ->name('chart-of-accounts.index');
    Route::post('/chart-of-accounts', [ChartOfAccountController::class, 'store'])
        ->name('chart-of-accounts.store');
    Route::get('/chart-of-accounts/select-options', [ChartOfAccountController::class, 'selectOptions'])
        ->name('chart-of-accounts.select-options');
    Route::get('/chart-of-accounts/types', [ChartOfAccountController::class, 'accountTypes'])
        ->name('chart-of-accounts.types');
    Route::get('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'show'])
        ->name('chart-of-accounts.show');
    Route::put('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'update'])
        ->name('chart-of-accounts.update');
    Route::delete('/chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'destroy'])
        ->name('chart-of-accounts.destroy');

    Route::get('/chart-of-account-categories', [ChartOfAccountCategoryController::class, 'index'])
        ->name('chart-of-account-categories.index');
    Route::post('/chart-of-account-categories', [ChartOfAccountCategoryController::class, 'store'])
        ->name('chart-of-account-categories.store');
    Route::get(
        '/chart-of-account-categories/select-options',
        [ChartOfAccountCategoryController::class, 'selectOptions']
    )->name('chart-of-account-categories.select-options');
    Route::get('/chart-of-account-categories/{chartOfAccountCategory}', [ChartOfAccountCategoryController::class, 'show'])
        ->name('chart-of-account-categories.show');
    Route::put('/chart-of-account-categories/{chartOfAccountCategory}', [ChartOfAccountCategoryController::class, 'update'])
        ->name('chart-of-account-categories.update');
    Route::delete('/chart-of-account-categories/{chartOfAccountCategory}', [ChartOfAccountCategoryController::class, 'destroy'])
        ->name('chart-of-account-categories.destroy');
});
