<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    private const TARGET_TRANSACTION_COUNT = 1000;

    /**
     * Seed believable transaction demo data.
     */
    public function run(): void
    {
        $this->call(ChartOfAccountSeeder::class);

        $missingTransactionCount = max(
            0,
            self::TARGET_TRANSACTION_COUNT - Transaction::query()->count()
        );

        if ($missingTransactionCount === 0) {
            return;
        }

        $accounts = ChartOfAccount::query()
            ->orderBy('code')
            ->get(['id', 'code', 'account_type', 'name']);

        Transaction::factory()
            ->count($missingTransactionCount)
            ->state(function () use ($accounts): array {
                $account = $accounts->random();
                $isIncome = $account->account_type === ChartOfAccount::ACCOUNT_TYPE_INCOME;
                $amount = fake()->numberBetween(10, $isIncome ? 8000 : 1500) * 1000;

                return [
                    'chart_of_account_id' => $account->id,
                    'description' => fake()->randomElement(self::descriptionsForAccount($account->code)),
                    'debit' => $isIncome ? 0 : $amount,
                    'credit' => $isIncome ? $amount : 0,
                ];
            })
            ->create();
    }

    /**
     * @return array<int, string>
     */
    private static function descriptionsForAccount(string $code): array
    {
        return match ($code) {
            '401' => ['Monthly salary', 'Salary adjustment', 'Regular payroll'],
            '402' => ['Executive honorarium', 'Leadership allowance', 'Committee compensation'],
            '403' => ['Trading profit withdrawal', 'Portfolio gain realized', 'Investment income'],
            '601' => ['School tuition', 'Book purchase', 'Semester activity fee'],
            '602' => ['Fuel refill', 'Vehicle maintenance fuel', 'Motorcycle petrol'],
            '603' => ['Mall parking', 'Office parking', 'Station parking'],
            '604' => ['Lunch meal', 'Team lunch', 'Client lunch'],
            '605' => ['Monthly groceries', 'Rice and staples', 'Household food supplies'],
            default => ['General transaction', 'Ledger adjustment', 'Miscellaneous payment'],
        };
    }
}
