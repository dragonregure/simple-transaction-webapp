<?php

namespace App\Providers;

use App\Contracts\ChartOfAccountRepositoryInterface;
use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Contracts\TransactionRepositoryInterface;
use App\Repositories\ChartOfAccountRepository;
use App\Repositories\ChartOfAccountCategoryRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ChartOfAccountRepositoryInterface::class,
            ChartOfAccountRepository::class
        );

        $this->app->bind(
            ChartOfAccountCategoryRepositoryInterface::class,
            ChartOfAccountCategoryRepository::class
        );

        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
