<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('', 302)
        ->header('Location', route('transactions.index', [], false));
});

Route::view('/transactions', 'transactions.index')
    ->name('transactions.index');

Route::view('/master/chart-of-accounts', 'chart-of-accounts.index')
    ->name('chart-of-accounts.index');

Route::view('/master/chart-of-account-categories', 'chart-of-account-categories.index')
    ->name('chart-of-account-categories.index');
