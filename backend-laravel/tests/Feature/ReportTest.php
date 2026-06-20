<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_displays_monthly_category_summary_for_selected_year(): void
    {
        $salary = $this->createAccount('401', 'Salary', 'Gaji Karyawan');
        $otherIncome = $this->createAccount('403', 'Other Income', 'Profit Trading');
        $meal = $this->createAccount('604', 'Meal Expense', 'Makan Siang');
        $this->createTransaction($salary, '2026-01-10', 0, 12000000);
        $this->createTransaction($otherIncome, '2026-01-20', 0, 5500000);
        $this->createTransaction($meal, '2026-01-21', 150000, 0);
        $this->createTransaction($salary, '2025-01-10', 0, 9000000);

        $this->get(route('reports.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('name="year"', false)
            ->assertSee(route('reports.export', ['year' => 2026], false), false)
            ->assertSee('2026-01')
            ->assertSee('2026-12')
            ->assertSee('Salary')
            ->assertSee('Other Income')
            ->assertSee('Meal Expense')
            ->assertSee('Total Income')
            ->assertSee('17,500,000')
            ->assertSee('Total Expense')
            ->assertSee('150,000')
            ->assertSee('Net Income')
            ->assertSee('17,350,000')
            ->assertDontSee('9,000,000');
    }

    public function test_report_export_downloads_excel_workbook(): void
    {
        if (! extension_loaded('zip')) {
            $this->markTestSkipped('The XLSX export assertion requires the zip extension.');
        }

        $salary = $this->createAccount('401', 'Salary', 'Gaji Karyawan');
        $meal = $this->createAccount('604', 'Meal Expense', 'Makan Siang');
        $this->createTransaction($salary, '2026-01-10', 0, 12000000);
        $this->createTransaction($meal, '2026-01-21', 150000, 0);

        $response = $this->get(route('reports.export', ['year' => 2026]));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename="transaction-report-2026.xlsx"');

        $temporaryFile = tempnam(sys_get_temp_dir(), 'report-');
        self::assertIsString($temporaryFile);
        file_put_contents($temporaryFile, $response->getContent());

        $workbook = IOFactory::load($temporaryFile);
        $sheet = $workbook->getActiveSheet();

        $this->assertSame('Category', $sheet->getCell('A1')->getValue());
        $this->assertSame('2026-01', $sheet->getCell('B1')->getValue());
        $this->assertSame('Amount', $sheet->getCell('B2')->getValue());
        $this->assertSame('Salary', $sheet->getCell('A3')->getValue());
        $this->assertSame(12000000, $sheet->getCell('B3')->getValue());
        $this->assertSame('Total Income', $sheet->getCell('A4')->getValue());
        $this->assertSame(12000000, $sheet->getCell('B4')->getValue());
        $this->assertSame('Meal Expense', $sheet->getCell('A5')->getValue());
        $this->assertSame(150000, $sheet->getCell('B5')->getValue());
        $this->assertSame('Total Expense', $sheet->getCell('A6')->getValue());
        $this->assertSame(150000, $sheet->getCell('B6')->getValue());
        $this->assertSame('Net Income', $sheet->getCell('A7')->getValue());
        $this->assertSame(11850000, $sheet->getCell('B7')->getValue());

        unlink($temporaryFile);
    }

    private function createAccount(string $code, string $categoryName, string $name): ChartOfAccount
    {
        $category = ChartOfAccountCategory::query()->firstOrCreate([
            'name' => $categoryName,
        ]);

        return ChartOfAccount::query()->create([
            'code' => $code,
            'category_id' => $category->id,
            'name' => $name,
        ]);
    }

    private function createTransaction(
        ChartOfAccount $account,
        string $date,
        int $debit,
        int $credit
    ): Transaction {
        return Transaction::query()->create([
            'chart_of_account_id' => $account->id,
            'transaction_date' => $date,
            'description' => 'Report transaction',
            'debit' => $debit,
            'credit' => $credit,
        ]);
    }
}
