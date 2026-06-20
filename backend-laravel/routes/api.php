<?php

use App\Http\Controllers\Api\V1\ChartOfAccountCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/chart-of-account-categories', [ChartOfAccountCategoryController::class, 'index'])
        ->name('chart-of-account-categories.index');
    Route::delete('/chart-of-account-categories/{chartOfAccountCategory}', [ChartOfAccountCategoryController::class, 'destroy'])
        ->name('chart-of-account-categories.destroy');
});
