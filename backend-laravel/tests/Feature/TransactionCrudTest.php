<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_renders_chart_of_account_select(): void
    {
        $account = $this->createAccount('401', 'Gaji Karyawan');

        $this->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('Create Transaction')
            ->assertSee(route('transactions.store', [], false), false)
            ->assertSee('name="transaction_date"', false)
            ->assertSee('name="chart_of_account_id"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="debit"', false)
            ->assertSee('name="credit"', false)
            ->assertSee((string) $account->id)
            ->assertSee('401 - Gaji Karyawan');
    }

    public function test_transaction_can_be_created(): void
    {
        $account = $this->createAccount('604', 'Makan Siang');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Lunch with client',
            'debit' => 125000,
            'credit' => 0,
        ])
            ->assertRedirect(route('transactions.index', [], false))
            ->assertHeader('Location', route('transactions.index', [], false))
            ->assertSessionHas('status', 'Transaction created.');

        $this->assertDatabaseHas('transactions', [
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Lunch with client',
            'debit' => 125000,
            'credit' => 0,
        ]);
    }

    public function test_transaction_requires_existing_chart_of_account(): void
    {
        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => 999,
            'description' => 'Unknown account transaction',
            'debit' => 125000,
            'credit' => 0,
        ])
            ->assertSessionHasErrors('chart_of_account_id');
    }

    public function test_transaction_requires_exactly_one_amount_side(): void
    {
        $account = $this->createAccount('602', 'Bensin');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Fuel refill',
            'debit' => 100000,
            'credit' => 100000,
        ])
            ->assertSessionHasErrors('debit');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Fuel refill',
            'debit' => 0,
            'credit' => 0,
        ])
            ->assertSessionHasErrors('debit');
    }

    public function test_edit_form_renders_existing_transaction_and_account_select(): void
    {
        $salary = $this->createAccount('401', 'Gaji Karyawan');
        $meal = $this->createAccount('604', 'Makan Siang');
        $transaction = Transaction::query()->create([
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $salary->id,
            'description' => 'Monthly salary',
            'debit' => 0,
            'credit' => 7500000,
        ]);

        $this->get(route('transactions.edit', $transaction))
            ->assertOk()
            ->assertSee('Update Transaction')
            ->assertSee(route('transactions.update', $transaction, false), false)
            ->assertSee('2026-06-10')
            ->assertSee('Monthly salary')
            ->assertSee('401 - Gaji Karyawan')
            ->assertSee('604 - Makan Siang')
            ->assertSee('value="' . $salary->id . '"', false)
            ->assertSee('value="' . $meal->id . '"', false);
    }

    public function test_transaction_can_be_updated(): void
    {
        $salary = $this->createAccount('401', 'Gaji Karyawan');
        $meal = $this->createAccount('604', 'Makan Siang');
        $transaction = Transaction::query()->create([
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $salary->id,
            'description' => 'Monthly salary',
            'debit' => 0,
            'credit' => 7500000,
        ]);

        $this->withSession(['_token' => 'test-token'])->put(route('transactions.update', $transaction), [
            '_token' => 'test-token',
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $meal->id,
            'description' => 'Team lunch',
            'debit' => 210000,
            'credit' => 0,
        ])
            ->assertRedirect(route('transactions.index', [], false))
            ->assertHeader('Location', route('transactions.index', [], false))
            ->assertSessionHas('status', 'Transaction updated.');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $meal->id,
            'description' => 'Team lunch',
            'debit' => 210000,
            'credit' => 0,
        ]);
    }

    public function test_transaction_can_be_deleted_through_api(): void
    {
        $account = $this->createAccount('603', 'Parkir');
        $transaction = Transaction::query()->create([
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Office parking',
            'debit' => 15000,
            'credit' => 0,
        ]);

        $this->deleteJson(route('api.v1.transactions.destroy', $transaction))
            ->assertNoContent();

        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    private function createAccount(string $code, string $name): ChartOfAccount
    {
        $category = ChartOfAccountCategory::query()->firstOrCreate([
            'name' => 'Test Category',
        ]);

        return ChartOfAccount::query()->create([
            'code' => $code,
            'category_id' => $category->id,
            'name' => $name,
        ]);
    }
}
