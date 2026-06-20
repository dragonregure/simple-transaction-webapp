<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Seed chart of account defaults.
     */
    public function run(): void
    {
        $this->call(ChartOfAccountCategorySeeder::class);

        $categoryIds = ChartOfAccountCategory::query()
            ->whereIn('name', [
                'Salary',
                'Other Income',
                'Family Expense',
                'Transport Expense',
                'Meal Expense',
            ])
            ->pluck('id', 'name');

        $accounts = [
            [
                'code' => '401',
                'category' => 'Salary',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
                'name' => 'Gaji Karyawan',
            ],
            [
                'code' => '402',
                'category' => 'Salary',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
                'name' => 'Gaji Ketua MPR',
            ],
            [
                'code' => '403',
                'category' => 'Other Income',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_INCOME,
                'name' => 'Profit Trading',
            ],
            [
                'code' => '601',
                'category' => 'Family Expense',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
                'name' => 'Biaya Sekolah',
            ],
            [
                'code' => '602',
                'category' => 'Transport Expense',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
                'name' => 'Bensin',
            ],
            [
                'code' => '603',
                'category' => 'Transport Expense',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
                'name' => 'Parkir',
            ],
            [
                'code' => '604',
                'category' => 'Meal Expense',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
                'name' => 'Makan Siang',
            ],
            [
                'code' => '605',
                'category' => 'Meal Expense',
                'account_type' => ChartOfAccount::ACCOUNT_TYPE_EXPENSE,
                'name' => 'Makanan Pokok Bulanan',
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::query()->updateOrCreate(
                ['code' => $account['code']],
                [
                    'category_id' => $categoryIds->get($account['category']),
                    'account_type' => $account['account_type'],
                    'name' => $account['name'],
                ],
            );
        }
    }
}
