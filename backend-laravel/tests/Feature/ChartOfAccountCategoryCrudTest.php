<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_renders(): void
    {
        $this->get(route('chart-of-account-categories.create'))
            ->assertOk()
            ->assertSee('Create Chart of Account Category')
            ->assertSee(route('chart-of-account-categories.store', [], false), false)
            ->assertSee('name="name"', false);
    }

    public function test_category_can_be_created(): void
    {
        $this->withSession(['_token' => 'test-token'])->post(route('chart-of-account-categories.store'), [
            '_token' => 'test-token',
            'name' => 'Education Expense',
        ])
            ->assertRedirect(route('chart-of-account-categories.index', [], false))
            ->assertHeader('Location', route('chart-of-account-categories.index', [], false))
            ->assertSessionHas('status', 'Chart of account category created.');

        $this->assertDatabaseHas('chart_of_account_categories', [
            'name' => 'Education Expense',
        ]);
    }

    public function test_category_create_requires_unique_name(): void
    {
        ChartOfAccountCategory::query()->create(['name' => 'Salary']);

        $this->withSession(['_token' => 'test-token'])->post(route('chart-of-account-categories.store'), [
            '_token' => 'test-token',
            'name' => 'Salary',
        ])
            ->assertSessionHasErrors('name');
    }

    public function test_edit_form_renders_existing_category(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);

        $this->get(route('chart-of-account-categories.edit', $category))
            ->assertOk()
            ->assertSee('Update Chart of Account Category')
            ->assertSee(route('chart-of-account-categories.update', $category, false), false)
            ->assertSee('Meal Expense');
    }

    public function test_category_can_be_updated(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);

        $this->withSession(['_token' => 'test-token'])->put(route('chart-of-account-categories.update', $category), [
            '_token' => 'test-token',
            'name' => 'Daily Meal Expense',
        ])
            ->assertRedirect(route('chart-of-account-categories.index', [], false))
            ->assertHeader('Location', route('chart-of-account-categories.index', [], false))
            ->assertSessionHas('status', 'Chart of account category updated.');

        $this->assertDatabaseHas('chart_of_account_categories', [
            'id' => $category->id,
            'name' => 'Daily Meal Expense',
        ]);
    }

    public function test_category_update_requires_unique_name(): void
    {
        ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        $category = ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);

        $this->withSession(['_token' => 'test-token'])->put(route('chart-of-account-categories.update', $category), [
            '_token' => 'test-token',
            'name' => 'Salary',
        ])
            ->assertSessionHasErrors('name');
    }

    public function test_category_can_be_deleted_through_api(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Transport Expense']);

        $this->deleteJson(route('api.v1.chart-of-account-categories.destroy', $category))
            ->assertNoContent();

        $this->assertDatabaseMissing('chart_of_account_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_in_use_cannot_be_deleted_through_api(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Family Expense']);
        ChartOfAccount::query()->create([
            'code' => '5001',
            'category_id' => $category->id,
            'name' => 'School Fee',
        ]);

        $this->deleteJson(route('api.v1.chart-of-account-categories.destroy', $category))
            ->assertConflict()
            ->assertJsonPath('message', 'This category cannot be deleted because it is used by chart of accounts.');

        $this->assertDatabaseHas('chart_of_account_categories', [
            'id' => $category->id,
        ]);
    }
}
