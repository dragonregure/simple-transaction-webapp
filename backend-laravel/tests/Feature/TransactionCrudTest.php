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

    public function test_create_form_renders_server_side_chart_of_account_select(): void
    {
        $account = $this->createAccount('401', 'Gaji Karyawan');

        $this->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('Create Transaction')
            ->assertSee(route('transactions.store', [], false), false)
            ->assertSee(route('api.v1.chart-of-accounts.select-options', [], false), false)
            ->assertSee('name="transaction_date"', false)
            ->assertSee('name="chart_of_account_id"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="amount"', false)
            ->assertDontSee('name="debit"', false)
            ->assertDontSee('name="credit"', false)
            ->assertSee('name="idempotency_key"', false)
            ->assertDontSee('value="' . $account->id . '"', false)
            ->assertDontSee('401 - Gaji Karyawan');
    }

    public function test_transaction_can_be_created(): void
    {
        $account = $this->createAccount('604', 'Makan Siang');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'idempotency_key' => 'd8fc3f37-a58f-4a68-b2f2-a03b12cf5695',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Lunch with client',
            'amount' => 125000,
        ])
            ->assertRedirect(route('transactions.index', [], false))
            ->assertHeader('Location', route('transactions.index', [], false))
            ->assertSessionHas('status', 'Transaction created.');

        $this->assertDatabaseHas('transactions', [
            'idempotency_key' => 'd8fc3f37-a58f-4a68-b2f2-a03b12cf5695',
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
            'idempotency_key' => '1ec2ad80-1b44-4a8d-9367-bc36fe12ecfb',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => 999,
            'description' => 'Unknown account transaction',
            'amount' => 125000,
        ])
            ->assertSessionHasErrors('chart_of_account_id');
    }

    public function test_transaction_requires_positive_amount(): void
    {
        $account = $this->createAccount('602', 'Bensin');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'idempotency_key' => '68bb282e-4525-4795-9f88-f03bc30a41a0',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Fuel refill',
            'amount' => 0,
        ])
            ->assertSessionHasErrors('amount');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'idempotency_key' => '932bd77c-f062-4901-8174-09636e1e1ba2',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Fuel refill',
            'amount' => '',
        ])
            ->assertSessionHasErrors('amount');
    }

    public function test_transaction_with_income_account_stores_amount_as_credit(): void
    {
        $account = $this->createAccount('401', 'Gaji Karyawan');

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'idempotency_key' => '8f08c405-eb9c-427f-968d-a8c144445df5',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Monthly salary',
            'amount' => 7500000,
        ])
            ->assertRedirect(route('transactions.index', [], false));

        $this->assertDatabaseHas('transactions', [
            'idempotency_key' => '8f08c405-eb9c-427f-968d-a8c144445df5',
            'debit' => 0,
            'credit' => 7500000,
        ]);
    }

    public function test_transaction_create_requires_unique_idempotency_key(): void
    {
        $account = $this->createAccount('605', 'Internet');
        Transaction::query()->create([
            'idempotency_key' => 'c71821a7-0f4b-4865-89d6-58a242f0ead9',
            'transaction_date' => '2026-06-09',
            'chart_of_account_id' => $account->id,
            'description' => 'Existing subscription',
            'debit' => 400000,
            'credit' => 0,
        ]);

        $this->withSession(['_token' => 'test-token'])->post(route('transactions.store'), [
            '_token' => 'test-token',
            'idempotency_key' => 'c71821a7-0f4b-4865-89d6-58a242f0ead9',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Duplicate subscription',
            'amount' => 400000,
        ])
            ->assertSessionHasErrors('idempotency_key');

        $this->assertDatabaseMissing('transactions', [
            'description' => 'Duplicate subscription',
        ]);
    }

    public function test_edit_form_renders_existing_transaction_and_selected_account(): void
    {
        $salary = $this->createAccount('401', 'Gaji Karyawan');
        $meal = $this->createAccount('604', 'Makan Siang');
        $transaction = Transaction::query()->create([
            'idempotency_key' => '9e75ae56-3480-4cd2-852f-f7b60bd41d8e',
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
            ->assertSee(route('api.v1.chart-of-accounts.select-options', [], false), false)
            ->assertSee('2026-06-10')
            ->assertSee('Monthly salary')
            ->assertSee('9e75ae56-3480-4cd2-852f-f7b60bd41d8e')
            ->assertSee('401 - Gaji Karyawan')
            ->assertSee('name="amount"', false)
            ->assertSee('value="7500000"', false)
            ->assertSee('value="' . $salary->id . '"', false)
            ->assertDontSee('value="' . $meal->id . '"', false)
            ->assertDontSee('604 - Makan Siang');
    }

    public function test_chart_of_account_select_options_are_searchable_and_paginated(): void
    {
        $this->createAccount('401', 'Gaji Karyawan');
        $this->createAccount('604', 'Makan Siang');

        foreach (range(1, 21) as $number) {
            $this->createAccount(sprintf('7%02d', $number), sprintf('Bulk Account %02d', $number));
        }

        $this->getJson(route('api.v1.chart-of-accounts.select-options', [
            'term' => 'Mak',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.text', '604 - Makan Siang')
            ->assertJsonPath('pagination.more', false);

        $this->getJson(route('api.v1.chart-of-accounts.select-options', [
            'q' => '7',
            'per_page' => 20,
        ]))
            ->assertOk()
            ->assertJsonCount(20, 'results')
            ->assertJsonPath('pagination.more', true);
    }

    public function test_transaction_can_be_updated(): void
    {
        $salary = $this->createAccount('401', 'Gaji Karyawan');
        $meal = $this->createAccount('604', 'Makan Siang');
        $transaction = Transaction::query()->create([
            'idempotency_key' => '801d960b-0bd6-454c-b916-82dedf93fd28',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $salary->id,
            'description' => 'Monthly salary',
            'debit' => 0,
            'credit' => 7500000,
        ]);

        $this->withSession(['_token' => 'test-token'])->put(route('transactions.update', $transaction), [
            '_token' => 'test-token',
            'idempotency_key' => '801d960b-0bd6-454c-b916-82dedf93fd28',
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $meal->id,
            'description' => 'Team lunch',
            'amount' => 210000,
        ])
            ->assertRedirect(route('transactions.index', [], false))
            ->assertHeader('Location', route('transactions.index', [], false))
            ->assertSessionHas('status', 'Transaction updated.');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'idempotency_key' => '801d960b-0bd6-454c-b916-82dedf93fd28',
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $meal->id,
            'description' => 'Team lunch',
            'debit' => 210000,
            'credit' => 0,
        ]);
    }

    public function test_transaction_update_requires_unique_idempotency_key_except_current_transaction(): void
    {
        $salary = $this->createAccount('406', 'Bonus');
        $meal = $this->createAccount('606', 'Meeting Meal');
        $existingTransaction = Transaction::query()->create([
            'idempotency_key' => '31e54fc4-1d02-4b66-8bcf-04cb2314325e',
            'transaction_date' => '2026-06-09',
            'chart_of_account_id' => $salary->id,
            'description' => 'Existing bonus',
            'debit' => 0,
            'credit' => 1200000,
        ]);
        $transaction = Transaction::query()->create([
            'idempotency_key' => '5d25306a-f1c2-4975-b58f-5f80909c841e',
            'transaction_date' => '2026-06-10',
            'chart_of_account_id' => $salary->id,
            'description' => 'Monthly salary',
            'debit' => 0,
            'credit' => 7500000,
        ]);

        $this->withSession(['_token' => 'test-token'])->put(route('transactions.update', $transaction), [
            '_token' => 'test-token',
            'idempotency_key' => $existingTransaction->idempotency_key,
            'transaction_date' => '2026-06-12',
            'chart_of_account_id' => $meal->id,
            'description' => 'Team lunch',
            'amount' => 210000,
        ])
            ->assertSessionHasErrors('idempotency_key');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'idempotency_key' => '5d25306a-f1c2-4975-b58f-5f80909c841e',
            'description' => 'Monthly salary',
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
            'account_type' => str_starts_with($code, '4')
                ? ChartOfAccount::ACCOUNT_TYPE_INCOME
                : ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
            'name' => $name,
        ]);
    }
}
