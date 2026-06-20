<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use Database\Seeders\ChartOfAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The index renders an AdminLTE table shell backed by the API endpoint.
     */
    public function test_index_renders_adminlte_table_shell(): void
    {
        $this->get(route('chart-of-accounts.index'))
            ->assertOk()
            ->assertSee('data-yajra-data-table', false)
            ->assertSee('class="table table-hover align-middle w-100 mb-0"', false)
            ->assertSee(route('api.v1.chart-of-accounts.index', [], false))
            ->assertSee(route('chart-of-accounts.create', [], false))
            ->assertDontSee('data-edit-endpoint-template', false)
            ->assertDontSee('data-delete-endpoint-template', false)
            ->assertSee('data-page-length-options', false);
    }

    /**
     * The API returns Yajra DataTables records for seeded chart of accounts.
     */
    public function test_api_displays_seeded_accounts(): void
    {
        $this->seed(ChartOfAccountSeeder::class);

        $this->getJson(route('api.v1.chart-of-accounts.index', $this->dataTableParameters()))
            ->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonPath('recordsTotal', 8)
            ->assertJsonPath('recordsFiltered', 8)
            ->assertJsonFragment(['code' => '401'])
            ->assertJsonFragment(['name' => 'Gaji Karyawan'])
            ->assertJsonFragment(['account_type' => 'Income'])
            ->assertJsonFragment(['category' => 'Salary'])
            ->assertJsonFragment(['actions' => $this->actionsForAccount('401')])
            ->assertJsonFragment(['code' => '403'])
            ->assertJsonFragment(['category' => 'Other Income'])
            ->assertJsonFragment(['code' => '605'])
            ->assertJsonFragment(['account_type' => 'Expense'])
            ->assertJsonFragment(['category' => 'Meal Expense']);
    }

    /**
     * Search is applied on the server for API table requests.
     */
    public function test_api_filters_accounts_by_search_term(): void
    {
        $salary = ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        $meal = ChartOfAccountCategory::query()->create(['name' => 'Meal Expense']);

        ChartOfAccount::query()->create([
            'code' => '401',
            'category_id' => $salary->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);
        ChartOfAccount::query()->create([
            'code' => '604',
            'category_id' => $meal->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Makan Siang',
        ]);

        $parameters = $this->dataTableParameters([
            'search' => ['value' => 'Gaji', 'regex' => 'false'],
        ]);

        $this->getJson(route('api.v1.chart-of-accounts.index', $parameters))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['name' => 'Gaji Karyawan'])
            ->assertJsonMissing(['name' => 'Makan Siang']);
    }

    /**
     * Sort query parameters control server-side ordering.
     */
    public function test_api_sorts_accounts_by_code(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);

        ChartOfAccount::query()->create([
            'code' => '999',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Zulu Account',
        ]);
        ChartOfAccount::query()->create([
            'code' => '111',
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Alpha Account',
        ]);

        $parameters = $this->dataTableParameters([
            'order' => [
                ['column' => 0, 'dir' => 'desc'],
            ],
        ]);

        $this->getJson(route('api.v1.chart-of-accounts.index', $parameters))
            ->assertOk()
            ->assertJsonPath('data.0.code', '999')
            ->assertJsonPath('data.1.code', '111');
    }

    /**
     * Relation sort query parameters control server-side ordering.
     */
    public function test_api_sorts_accounts_by_category_name(): void
    {
        $salary = ChartOfAccountCategory::query()->create(['name' => 'Salary']);
        $family = ChartOfAccountCategory::query()->create(['name' => 'Family Expense']);

        ChartOfAccount::query()->create([
            'code' => '401',
            'category_id' => $salary->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Gaji Karyawan',
        ]);
        ChartOfAccount::query()->create([
            'code' => '601',
            'category_id' => $family->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => 'Biaya Sekolah',
        ]);

        $parameters = $this->dataTableParameters([
            'order' => [
                ['column' => 3, 'dir' => 'asc'],
            ],
        ]);

        $this->getJson(route('api.v1.chart-of-accounts.index', $parameters))
            ->assertOk()
            ->assertJsonPath('data.0.category', 'Family Expense')
            ->assertJsonPath('data.1.category', 'Salary');
    }

    /**
     * Per-page filters are applied to server-side pagination.
     */
    public function test_api_paginates_accounts_with_requested_page_size(): void
    {
        $category = ChartOfAccountCategory::query()->create(['name' => 'Salary']);

        foreach (range(1, 12) as $number) {
            ChartOfAccount::query()->create([
                'code' => sprintf('%03d', $number),
                'category_id' => $category->id,
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
                'name' => sprintf('Account %02d', $number),
            ]);
        }

        $parameters = $this->dataTableParameters(['length' => 5]);

        $this->getJson(route('api.v1.chart-of-accounts.index', $parameters))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 12)
            ->assertJsonPath('recordsFiltered', 12)
            ->assertJsonPath('data.0.code', '001')
            ->assertJsonCount(5, 'data')
            ->assertJsonMissing(['code' => '006']);
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
                    'data' => 'code',
                    'name' => 'code',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'name',
                    'name' => 'name',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'account_type',
                    'name' => 'account_type',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'category',
                    'name' => 'category.name',
                    'searchable' => 'true',
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

    private function actionsForAccount(string $code): string
    {
        $account = ChartOfAccount::query()
            ->where('code', $code)
            ->firstOrFail();

        return view('chart-of-accounts.partials.table-actions', [
            'account' => $account,
        ])->render();
    }
}
