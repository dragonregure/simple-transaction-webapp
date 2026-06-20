<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The root URL should open the default transaction workspace.
     */
    public function test_root_url_redirects_to_transactions_index(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/transactions');
    }

    /**
     * The main navigation pages should render the AdminLTE shell.
     */
    public function test_admin_navigation_pages_are_available(): void
    {
        $pages = [
            route('transactions.index') => 'Transactions',
            route('reports.index') => 'Reports',
            route('chart-of-accounts.index') => 'Master Chart of Accounts',
            route('chart-of-account-categories.index') => 'Master Chart of Account Categories',
        ];

        foreach ($pages as $url => $title) {
            $this->get($url)
                ->assertOk()
                ->assertSee($title)
                ->assertSee('app-sidebar', false)
                ->assertSee('href="/vendor/adminlte/css/adminlte.min.css"', false)
                ->assertSee('href="/vendor/bootstrap-icons/font/bootstrap-icons.min.css"', false);
        }
    }
}
