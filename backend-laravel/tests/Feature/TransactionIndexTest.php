<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Database\Seeders\TransactionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_adminlte_table_shell(): void
    {
        $this->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('data-yajra-data-table', false)
            ->assertSee('class="table table-hover align-middle w-100 mb-0"', false)
            ->assertSee(route('api.v1.transactions.index', [], false))
            ->assertSee(route('transactions.create', [], false))
            ->assertSee('Chart of Account Code')
            ->assertSee('Chart of Account Name')
            ->assertSee('Debit')
            ->assertSee('Credit')
            ->assertSee('data-page-length-options', false);
    }

    public function test_api_displays_transactions_with_account_columns(): void
    {
        $account = $this->createAccount('604', 'Makan Siang');
        $transaction = Transaction::query()->create([
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Team lunch',
            'debit' => 210000,
            'credit' => 0,
        ]);

        $this->getJson(route('api.v1.transactions.index', $this->dataTableParameters()))
            ->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['transaction_date' => '2026-06-10'])
            ->assertJsonFragment(['chart_of_account_code' => '604'])
            ->assertJsonFragment(['chart_of_account_name' => 'Makan Siang'])
            ->assertJsonFragment(['description' => 'Team lunch'])
            ->assertJsonFragment(['debit' => '210,000'])
            ->assertJsonFragment(['credit' => '0'])
            ->assertJsonFragment(['actions' => $this->actionsForTransaction($transaction)]);
    }

    public function test_api_filters_transactions_by_account_name(): void
    {
        $salary = $this->createAccount('401', 'Gaji Karyawan');
        $meal = $this->createAccount('604', 'Makan Siang');

        Transaction::query()->create([
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $salary->id,
            'description' => 'Monthly salary',
            'debit' => 0,
            'credit' => 7500000,
        ]);
        Transaction::query()->create([
            'transaction_date' => '2026-06-11',
            'chart_of_account_id' => $meal->id,
            'description' => 'Team lunch',
            'debit' => 210000,
            'credit' => 0,
        ]);

        $parameters = $this->dataTableParameters([
            'search' => ['value' => 'Makan', 'regex' => 'false'],
        ]);

        $this->getJson(route('api.v1.transactions.index', $parameters))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['description' => 'Team lunch'])
            ->assertJsonMissing(['description' => 'Monthly salary']);
    }

    public function test_api_sorts_transactions_by_date(): void
    {
        $account = $this->createAccount('602', 'Bensin');

        Transaction::query()->create([
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Older fuel refill',
            'debit' => 100000,
            'credit' => 0,
        ]);
        Transaction::query()->create([
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $account->id,
            'description' => 'Newer fuel refill',
            'debit' => 120000,
            'credit' => 0,
        ]);

        $parameters = $this->dataTableParameters([
            'order' => [
                ['column' => 0, 'dir' => 'desc'],
            ],
        ]);

        $this->getJson(route('api.v1.transactions.index', $parameters))
            ->assertOk()
            ->assertJsonPath('data.0.transaction_date', '2026-06-12')
            ->assertJsonPath('data.1.transaction_date', '2026-06-10');
    }

    public function test_transaction_seeder_creates_demo_transaction_volume(): void
    {
        $this->seed(TransactionSeeder::class);

        $this->assertDatabaseCount('transactions', 1000);
        $this->assertDatabaseHas('transactions', [
            'deleted_at' => null,
        ]);
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
                    'data' => 'transaction_date',
                    'name' => 'transaction_date',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'chart_of_account_code',
                    'name' => 'chart_of_accounts.code',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'chart_of_account_name',
                    'name' => 'chart_of_accounts.name',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'description',
                    'name' => 'description',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'debit',
                    'name' => 'debit',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => ['value' => '', 'regex' => 'false'],
                ],
                [
                    'data' => 'credit',
                    'name' => 'credit',
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

    private function actionsForTransaction(Transaction $transaction): string
    {
        return view('transactions.partials.table-actions', [
            'transaction' => $transaction,
        ])->render();
    }

    private function createAccount(string $code, string $name): ChartOfAccount
    {
        $category = ChartOfAccountCategory::query()->firstOrCreate([
            'name' => 'Test Category',
        ]);

        return ChartOfAccount::query()->create([
            'code' => $code,
            'category_id' => $category->id,
            'account_type' => str_starts_with($code, '4')
                ? ChartOfAccount::ACCOUNT_TYPE_INCOME
                : ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => $name,
        ]);
    }
}
