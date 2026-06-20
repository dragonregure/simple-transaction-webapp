<?php

namespace Database\Seeders;

use App\Models\ChartOfAccountCategory;
use Illuminate\Database\Seeder;

class ChartOfAccountCategorySeeder extends Seeder
{
    /**
     * Seed chart of account category defaults.
     */
    public function run(): void
    {
        $categories = [
            'Salary',
            'Other Income',
            'Family Expense',
            'Transport Expense',
            'Meal Expense',
        ];

        foreach ($categories as $category) {
            ChartOfAccountCategory::query()->updateOrCreate(
                ['name' => $category],
                ['name' => $category],
            );
        }
    }
}
