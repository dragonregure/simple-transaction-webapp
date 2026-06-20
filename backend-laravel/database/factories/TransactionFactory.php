<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account = $this->chartOfAccount();
        $isIncome = $account->account_type === ChartOfAccount::ACCOUNT_TYPE_INCOME;
        $amount = $this->faker->numberBetween(10, 2500) * 1000;

        return [
            'chart_of_account_id' => $account->id,
            'transaction_date' => $this->faker->dateTimeBetween('-120 months', 'now')->format('Y-m-d'),
            'description' => $this->faker->randomElement([
                'Monthly payroll receipt',
                'Freelance project payment',
                'Grocery shopping',
                'School tuition installment',
                'Fuel purchase',
                'Parking fee',
                'Lunch with team',
                'Household supplies',
                'Trading profit withdrawal',
                'Public transport fare',
            ]),
            'debit' => $isIncome ? 0 : $amount,
            'credit' => $isIncome ? $amount : 0,
        ];
    }

    private function chartOfAccount(): ChartOfAccount
    {
        $existingAccount = ChartOfAccount::query()
            ->inRandomOrder()
            ->first(['id', 'account_type']);

        if ($existingAccount instanceof ChartOfAccount) {
            return $existingAccount;
        }

        $category = ChartOfAccountCategory::query()->firstOrCreate([
            'name' => 'Other Income',
        ]);

        return ChartOfAccount::query()->create([
            'code' => (string) $this->faker->unique()->numberBetween(700, 999),
            'category_id' => $category->id,
            'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
            'name' => 'Seeded Account',
        ]);
    }
}
