<?php

namespace App\Providers;

use App\Contracts\ChartOfAccountRepositoryInterface;
use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Repositories\ChartOfAccountRepository;
use App\Repositories\ChartOfAccountCategoryRepository;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
