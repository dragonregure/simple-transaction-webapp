<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_renders_server_side_category_select(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);

        $this->get(route('chart-of-accounts.create'))
            ->assertOk()
            ->assertSee('Create Chart of Account')
            ->assertSee(route('chart-of-accounts.store', [], false), false)
            ->assertSee(route('api.v1.chart-of-account-categories.select-options', [], false), false)
            ->assertSee('name="code"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('name="account_type"', false)
            ->assertSee('Income')
            ->assertSee('Expense')
            ->assertDontSee('value="' . $category->id . '"', false)
            ->assertDontSee('Salary');
    }

    public function test_account_can_be_created(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);

        $this->withSession(['_token' => 'test-token'])->post(route('chart-of-accounts.store'), [
            '_token' => 'test-token',
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ])
            ->assertRedirect(route('chart-of-accounts.index', [], false))
            ->assertHeader('Location', route('chart-of-accounts.index', [], false))
            ->assertSessionHas('status', 'Chart of account created.');

        $this->assertDatabaseHas('chart_of_accounts', [
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);
    }

    public function test_account_create_requires_unique_code(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        ChartOfAccount::query()->create([
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);

        $this->withSession(['_token' => 'test-token'])->post(route('chart-of-accounts.store'), [
            '_token' => 'test-token',
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Ketua MPR',
        ])
            ->assertSessionHasErrors('code');
    }

    public function test_account_create_requires_existing_category(): void
    {
        $this->withSession(['_token' => 'test-token'])->post(route('chart-of-accounts.store'), [
            '_token' => 'test-token',
            'code' => '401',
            'category_id' => 999,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ])
            ->assertSessionHasErrors('category_id');
    }

    public function test_account_create_requires_valid_account_type(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);

        $this->withSession(['_token' => 'test-token'])->post(route('chart-of-accounts.store'), [
            '_token' => 'test-token',
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => 'asset',
            'name' => 'Gaji Karyawan',
        ])
            ->assertSessionHasErrors('account_type');
    }

    public function test_edit_form_renders_existing_account_and_selected_category(): void
    {
        $salary = ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        $meal = ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);
        $account = ChartOfAccount::query()->create([
            'code' => '401',
            'category_id' => $salary->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);

        $this->get(route('chart-of-accounts.edit', $account))
            ->assertOk()
            ->assertSee('Update Chart of Account')
            ->assertSee(route('chart-of-accounts.update', $account, false), false)
            ->assertSee(route('api.v1.chart-of-account-categories.select-options', [], false), false)
            ->assertSee('401')
            ->assertSee('Gaji Karyawan')
            ->assertSee('Salary')
            ->assertSee('Income')
            ->assertSee('Expense')
            ->assertSee('value="' . $salary->id . '"', false)
            ->assertDontSee('value="' . $meal->id . '"', false)
            ->assertDontSee('Meal Expense');
    }

    public function test_category_select_options_are_searchable_and_paginated(): void
    {
        ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);

        foreach (range(1, 21) as $number) {
            ChartOfAccountCategory::query()->create(['name' => sprintf('Category %02d', $number)]);
        }

        $this->getJson(route('api.v1.chart-of-account-categories.select-options', [
            'term' => 'Sa',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.text', 'Salary')
            ->assertJsonPath('pagination.more', false);

        $this->getJson(route('api.v1.chart-of-account-categories.select-options', [
            'per_page' => 20,
        ]))
            ->assertOk()
            ->assertJsonCount(20, 'results')
            ->assertJsonPath('pagination.more', true);
    }

    public function test_account_can_be_updated(): void
    {
        $salary = ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        $meal = ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);
        $account = ChartOfAccount::query()->create([
            'code' => '401',
            'category_id' => $salary->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);

        $this->withSession(['_token' => 'test-token'])->put(route('chart-of-accounts.update', $account), [
            '_token' => 'test-token',
            'code' => '604',
            'category_id' => $meal->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Makan Siang',
        ])
            ->assertRedirect(route('chart-of-accounts.index', [], false))
            ->assertHeader('Location', route('chart-of-accounts.index', [], false))
            ->assertSessionHas('status', 'Chart of account updated.');

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'code' => '604',
            'category_id' => $meal->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Makan Siang',
        ]);
    }

    public function test_account_update_requires_unique_code(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        ChartOfAccount::query()->create([
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);
        $account = ChartOfAccount::query()->create([
            'code' => '402',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Ketua MPR',
        ]);

        $this->withSession(['_token' => 'test-token'])->put(route('chart-of-accounts.update', $account), [
            '_token' => 'test-token',
            'code' => '401',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Ketua MPR',
        ])
            ->assertSessionHasErrors('code');
    }

    public function test_account_can_be_deleted_through_api(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Transport Expense']);
        $account = ChartOfAccount::query()->create([
            'code' => '602',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Bensin',
        ]);

        $this->deleteJson(route('api.v1.chart-of-accounts.destroy', $account))
            ->assertNoContent();

        $this->assertDatabaseMissing('chart_of_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_account_in_use_cannot_be_deleted_through_api(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Family Expense']);
        $account = ChartOfAccount::query()->create([
            'code' => '601',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Biaya Sekolah',
        ]);
        Transaction::query()->create([
            'chart_of_account_id' => $account->id,
            'description' => 'School fee',
            'debit' => 100000,
            'credit' => 0,
        ]);

        $this->deleteJson(route('api.v1.chart-of-accounts.destroy', $account))
            ->assertConflict()
            ->assertJsonPath('message', 'This chart of account cannot be deleted because it is used by transactions.');

        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
        ]);
    }
}
