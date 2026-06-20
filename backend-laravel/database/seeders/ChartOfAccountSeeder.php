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
            ['code' => '401', 'category' => 'Salary', 'name' => 'Gaji Karyawan'],
            ['code' => '402', 'category' => 'Salary', 'name' => 'Gaji Ketua MPR'],
            ['code' => '403', 'category' => 'Other Income', 'name' => 'Profit Trading'],
            ['code' => '601', 'category' => 'Family Expense', 'name' => 'Biaya Sekolah'],
            ['code' => '602', 'category' => 'Transport Expense', 'name' => 'Bensin'],
            ['code' => '603', 'category' => 'Transport Expense', 'name' => 'Parkir'],
            ['code' => '604', 'category' => 'Meal Expense', 'name' => 'Makan Siang'],
            ['code' => '605', 'category' => 'Meal Expense', 'name' => 'Makanan Pokok Bulanan'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::query()->updateOrCreate(
                ['code' => $account['code']],
                [
                    'category_id' => $categoryIds->get($account['category']),
                    'name' => $account['name'],
                ],
            );
        }
    }
}
