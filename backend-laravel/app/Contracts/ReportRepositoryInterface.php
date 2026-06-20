<?php

namespace App\Contracts;

interface ReportRepositoryInterface
{
    /**
     * @return array<int, int>
     */
    public function availableYears(): array;

    /**
     * @return array{
     *     year: int,
     *     months: array<int, string>,
     *     income_rows: array<int, array{label: string, amounts: array<int, int>}>,
     *     expense_rows: array<int, array{label: string, amounts: array<int, int>}>,
     *     total_income: array<int, int>,
     *     total_expense: array<int, int>,
     *     net_income: array<int, int>
     * }
     */
    public function monthlyCategorySummary(int $year): array;
}
