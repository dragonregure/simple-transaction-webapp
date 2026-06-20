<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_can_be_created_loaded_and_updated_through_api(): void
    {
        $salary = $this->createAccount('401', 'Salary', ChartOfAccount::ACCOUNT_TYPE_INCOME);
        $meal = $this->createAccount('604', 'Meal', ChartOfAccount::ACCOUNT_TYPE_EXPENSE);

        $createResponse = $this->postJson(route('api.v1.transactions.store'), [
            'idempotency_key' => 'e4c35216-f637-4e8d-9a40-77a1d2c2c62f',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $salary->id,
            'description' => 'API salary',
            'amount' => 7500000,
        ])
            ->assertCreated()
            ->assertJsonPath('data.chart_of_account.text', '401 - Salary')
            ->assertJsonPath('data.amount', 7500000)
            ->assertJsonPath('data.credit', 7500000);

        $transactionId = $createResponse->json('data.id');

        $this->getJson(route('api.v1.transactions.show', $transactionId))
            ->assertOk()
            ->assertJsonPath('data.description', 'API salary');

        $this->putJson(route('api.v1.transactions.update', $transactionId), [
            'idempotency_key' => 'e4c35216-f637-4e8d-9a40-77a1d2c2c62f',
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $meal->id,
            'description' => 'API meal',
            'amount' => 125000,
        ])
            ->assertOk()
            ->assertJsonPath('data.chart_of_account.text', '604 - Meal')
            ->assertJsonPath('data.debit', 125000)
            ->assertJsonPath('data.credit', 0);
    }

    public function test_chart_of_account_api_supports_options_crud_and_types(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Operating Income']);

        $createResponse = $this->postJson(route('api.v1.chart-of-accounts.store'), [
            'code' => '402',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Consulting',
        ])
            ->assertCreated()
            ->assertJsonPath('data.category.name', 'Operating Income');

        $accountId = $createResponse->json('data.id');

        $this->getJson(route('api.v1.chart-of-accounts.types'))
            ->assertOk()
            ->assertJsonFragment(['value' => 'income', 'label' => 'Income'])
            ->assertJsonFragment(['value' => 'expense', 'label' => 'Expense']);

        $this->getJson(route('api.v1.chart-of-accounts.select-options', ['term' => 'Cons']))
            ->assertOk()
            ->assertJsonPath('results.0.text', '402 - Consulting');

        $this->putJson(route('api.v1.chart-of-accounts.update', $accountId), [
            'code' => '403',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Consulting Revenue',
        ])
            ->assertOk()
            ->assertJsonPath('data.code', '403');
    }

    public function test_chart_of_account_category_api_supports_crud_and_options(): void
    {
        $createResponse = $this->postJson(route('api.v1.chart-of-account-categories.store'), [
            'name' => 'Travel',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Travel');

        $categoryId = $createResponse->json('data.id');

        $this->getJson(route('api.v1.chart-of-account-categories.select-options', ['term' => 'Tra']))
            ->assertOk()
            ->assertJsonPath('results.0.text', 'Travel');

        $this->putJson(route('api.v1.chart-of-account-categories.update', $categoryId), [
            'name' => 'Travel Expense',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Travel Expense');
    }

    public function test_report_api_returns_summary_and_export(): void
    {
        $income = $this->createAccount('401', 'Salary', ChartOfAccount::ACCOUNT_TYPE_INCOME);
        $expense = $this->createAccount('601', 'Meal', ChartOfAccount::ACCOUNT_TYPE_EXPENSE);

        $this->createTransaction($income, '2026-01-10', 0, 3000000);
        $this->createTransaction($expense, '2026-01-11', 250000, 0);

        $this->getJson(route('api.v1.reports.index', ['year' => 2026]))
            ->assertOk()
            ->assertJsonPath('data.selected_year', 2026)
            ->assertJsonPath('data.report.total_income.1', 3000000)
            ->assertJsonPath('data.report.total_expense.1', 250000)
            ->assertJsonPath('data.report.net_income.1', 2750000);

        if (! extension_loaded('zip')) {
            $this->markTestSkipped('The XLSX export assertion requires the zip extension.');
        }

        $this->get(route('api.v1.reports.export', ['year' => 2026]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_swagger_documentation_is_available(): void
    {
        $this->get('/api/documentation')
            ->assertOk()
            ->assertSee('Simple Transaction API Docs');

        $this->get('/api/docs')
            ->assertOk()
            ->assertSee('openapi: 3.0.3')
            ->assertSee('Simple Transaction API');
    }

    private function createAccount(string $code, string $name, string $accountType): ChartOfAccount
    {
        $category = ChartOfAccountCategory::query()->firstOrCreate([
            'name' => $name . ' Category',
        ]);

        return ChartOfAccount::query()->create([
            'code' => $code,
            'category_id' => $category->id,
            'account_type' => $accountType,
            'name' => $name,
        ]);
    }

    private function createTransaction(
        ChartOfAccount $account,
        string $date,
        int $debit,
        int $credit
    ): Transaction {
        return Transaction::query()->create([
            'chart_of_account_id' => $account->id,
            'transaction_date' => $date,
            'description' => 'API report transaction',
            'debit' => $debit,
            'credit' => $credit,
        ]);
    }
}
