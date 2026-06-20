<?php

namespace App\Repositories;

use App\Contracts\ReportRepositoryInterface;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @return array<int, int>
     */
    public function availableYears(): array
    {
        $years = Transaction::query()
            ->whereNotNull('transaction_date')
            ->orderByDesc('transaction_date')
            ->get(['transaction_date'])
            ->map(
                fn (Transaction $transaction): int => (int) CarbonImmutable::parse(
                    (string) $transaction->transaction_date
                )->format('Y')
            )
            ->filter(fn (int $year): bool => $year > 0)
            ->unique()
            ->values()
            ->all();

        if ($years === []) {
            return [(int) now()->format('Y')];
        }

        return $years;
    }

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
    public function monthlyCategorySummary(int $year): array
    {
        $months = $this->monthsForYear($year);
        $incomeRows = [];
        $expenseRows = [];
        $categoryIndex = [];

        foreach ($this->reportCategories() as $category) {
            $firstAccount = $category->accounts->first();

            if (! $firstAccount instanceof ChartOfAccount) {
                continue;
            }

            $type = $this->accountType($firstAccount);
            $row = [
                'label' => $category->name,
                'amounts' => $this->emptyMonthlyAmounts(),
            ];

            if ($type === 'income') {
                $incomeRows[] = $row;
                $categoryIndex[$category->id] = ['type' => $type, 'index' => count($incomeRows) - 1];

                continue;
            }

            $expenseRows[] = $row;
            $categoryIndex[$category->id] = ['type' => $type, 'index' => count($expenseRows) - 1];
        }

        $totalIncome = $this->emptyMonthlyAmounts();
        $totalExpense = $this->emptyMonthlyAmounts();

        foreach ($this->transactionsForYear($year) as $transaction) {
            $account = $transaction->chartOfAccount;
            $category = $account?->category;
            if ($account === null || $category === null || $transaction->transaction_date === null) {
                continue;
            }

            $categoryKey = $categoryIndex[$category->id] ?? null;

            if ($categoryKey === null) {
                continue;
            }

            $month = (int) CarbonImmutable::parse((string) $transaction->transaction_date)->format('n');
            $type = $this->accountType($account);
            $amount = $this->transactionAmount($transaction, $type);

            if ($type === 'income') {
                $incomeRows[$categoryKey['index']]['amounts'][$month] += $amount;
                $totalIncome[$month] += $amount;

                continue;
            }

            $expenseRows[$categoryKey['index']]['amounts'][$month] += $amount;
            $totalExpense[$month] += $amount;
        }

        $netIncome = $this->emptyMonthlyAmounts();

        foreach (array_keys($months) as $month) {
            $netIncome[$month] = $totalIncome[$month] - $totalExpense[$month];
        }

        return [
            'year' => $year,
            'months' => $months,
            'income_rows' => $incomeRows,
            'expense_rows' => $expenseRows,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function monthsForYear(int $year): array
    {
        $months = [];

        foreach (range(1, 12) as $month) {
            $months[$month] = sprintf('%d-%02d', $year, $month);
        }

        return $months;
    }

    /**
     * @return array<int, int>
     */
    private function emptyMonthlyAmounts(): array
    {
        return array_fill_keys(range(1, 12), 0);
    }

    /**
     * @return Collection<int, ChartOfAccountCategory>
     */
    private function reportCategories(): Collection
    {
        return ChartOfAccountCategory::query()
            ->with(['accounts' => fn ($query) => $query->orderBy('code')])
            ->whereHas('accounts')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, Transaction>
     */
    private function transactionsForYear(int $year): Collection
    {
        return Transaction::query()
            ->with(['chartOfAccount.category'])
            ->whereBetween('transaction_date', [
                sprintf('%d-01-01', $year),
                sprintf('%d-12-31', $year),
            ])
            ->orderBy('transaction_date')
            ->get();
    }

    private function accountType(ChartOfAccount $account): string
    {
        return str_starts_with($account->code, '4') ? 'income' : 'expense';
    }

    private function transactionAmount(Transaction $transaction, string $type): int
    {
        if ($type === 'income') {
            return $transaction->credit - $transaction->debit;
        }

        return $transaction->debit - $transaction->credit;
    }
}
