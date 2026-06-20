<?php

namespace Tests\Feature;

use App\Models\ChartOfAccountCategory;
use Database\Seeders\ChartOfAccountCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountCategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The index renders an AdminLTE table shell backed by the API endpoint.
     */
    public function test_index_renders_adminlte_table_shell(): void
    {
        $this->get(route('chart-of-account-categories.index'))
            ->assertOk()
            ->assertSee('data-yajra-data-table', false)
            ->assertSee('class="table table-hover align-middle w-100 mb-0"', false)
            ->assertSee(route('api.v1.chart-of-account-categories.index', [], false))
            ->assertSee(route('chart-of-account-categories.create', [], false))
            ->assertDontSee('data-edit-endpoint-template', false)
            ->assertDontSee('data-delete-endpoint-template', false)
            ->assertSee('data-page-length-options', false);
    }

    /**
     * The API returns Yajra DataTables records for seeded chart of account categories.
     */
    public function test_api_displays_seeded_categories(): void
    {
        $this->seed(ChartOfAccountCategorySeeder::class);

        $this->getJson(route('api.v1.chart-of-account-categories.index', $this->dataTableParameters()))
            ->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonPath('recordsTotal', 5)
            ->assertJsonPath('recordsFiltered', 5)
            ->assertJsonFragment(['name' => 'Salary'])
            ->assertJsonFragment(['actions' => $this->actionsForCategory('Salary')])
            ->assertJsonFragment(['name' => 'Other Income'])
            ->assertJsonFragment(['name' => 'Family Expense'])
            ->assertJsonFragment(['name' => 'Transport Expense'])
            ->assertJsonFragment(['name' => 'Meal Expense']);
    }

    /**
     * Search is applied on the server for API table requests.
     */
    public function test_api_filters_categories_by_search_term(): void
    {
        ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);

        $parameters = $this->dataTableParameters([
            'search' => ['value' => 'Salary', 'regex' => 'false'],
        ]);

        $this->getJson(route('api.v1.chart-of-account-categories.index', $parameters))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['name' => 'Salary'])
            ->assertJsonMissing(['name' => 'Meal Expense']);
    }

    /**
     * Sort query parameters control server-side ordering.
     */
    public function test_api_sorts_categories_by_name(): void
    {
        ChartOfAccountCategory::query()->create(['name' => 'Alpha Category']);
        ChartOfAccountCategory::query()->create(['name' => 'Zulu Category']);

        $parameters = $this->dataTableParameters([
            'order' => [
                ['column' => 0, 'dir' => 'desc'],
            ],
        ]);

        $this->getJson(route('api.v1.chart-of-account-categories.index', $parameters))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Zulu Category')
            ->assertJsonPath('data.1.name', 'Alpha Category');
    }

    /**
     * Per-page filters are applied to server-side pagination.
     */
    public function test_api_paginates_categories_with_requested_page_size(): void
    {
        foreach (range(1, 12) as $number) {
            ChartOfAccountCategory::query()->create(['name' => sprintf('Category %02d', $number)]);
        }

        $parameters = $this->dataTableParameters(['length' => 5]);

        $this->getJson(route('api.v1.chart-of-account-categories.index', $parameters))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 12)
            ->assertJsonPath('recordsFiltered', 12)
            ->assertJsonPath('data.0.name', 'Category 01')
            ->assertJsonCount(5, 'data')
            ->assertJsonMissing(['name' => 'Category 06']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function dataTableParameters(array $overrides = []): array
    {
        return array_replace_recursive([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ],
            'columns' => [
                [
                    'data' => 'name',
                    'name' => 'name',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'created_at',
                    'name' => 'created_at',
                    'searchable' => 'false',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'actions',
                    'name' => 'actions',
                    'searchable' => 'false',
                    'orderable' => 'false',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
            ],
        ], $overrides);
    }

    private function actionsForCategory(string $name): string
    {
        $category = ChartOfAccountCategory::query()
            ->where('name', $name)
            ->firstOrFail();

        return view('chart-of-account-categories.partials.table-actions', [
            'category' => $category,
        ])->render();
    }
}
