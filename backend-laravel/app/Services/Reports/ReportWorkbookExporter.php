<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportWorkbookExporter
{
    private const HEADER_FILL = 'FFFFFF00';
    private const INCOME_FILL = 'FFC5E0B3';
    private const TOTAL_INCOME_FILL = 'FFA8D08D';
    private const EXPENSE_FILL = 'FFF7CAAC';
    private const TOTAL_EXPENSE_FILL = 'FFF4B083';

    /**
     * @param array{
     *     year: int,
     *     months: array<int, string>,
     *     income_rows: array<int, array{label: string, amounts: array<int, int>}>,
     *     expense_rows: array<int, array{label: string, amounts: array<int, int>}>,
     *     total_income: array<int, int>,
     *     total_expense: array<int, int>,
     *     net_income: array<int, int>
     * } $report
     */
    public function export(array $report): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report ' . $report['year']);

        $lastColumn = Coordinate::stringFromColumnIndex(count($report['months']) + 1);
        $this->writeHeader($sheet, $report, $lastColumn);

        $rowNumber = 3;
        $rowNumber = $this->writeRows($sheet, $report['income_rows'], $rowNumber, self::INCOME_FILL);
        $this->writeAmountRow($sheet, 'Total Income', $report['total_income'], $rowNumber, self::TOTAL_INCOME_FILL);
        $rowNumber++;

        $rowNumber = $this->writeRows($sheet, $report['expense_rows'], $rowNumber, self::EXPENSE_FILL);
        $this->writeAmountRow($sheet, 'Total Expense', $report['total_expense'], $rowNumber, self::TOTAL_EXPENSE_FILL);
        $rowNumber++;

        $this->writeAmountRow($sheet, 'Net Income', $report['net_income'], $rowNumber);

        $sheet->getColumnDimension('A')->setWidth(26);

        foreach (range(2, count($report['months']) + 1) as $columnIndex) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setWidth(14);
        }

        $sheet->freezePane('B3');

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $contents = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return is_string($contents) ? $contents : '';
    }

    /**
     * @param array{
     *     year: int,
     *     months: array<int, string>,
     *     income_rows: array<int, array{label: string, amounts: array<int, int>}>,
     *     expense_rows: array<int, array{label: string, amounts: array<int, int>}>,
     *     total_income: array<int, int>,
     *     total_expense: array<int, int>,
     *     net_income: array<int, int>
     * } $report
     */
    private function writeHeader(Worksheet $sheet, array $report, string $lastColumn): void
    {
        $sheet->mergeCells('A1:A2');
        $sheet->setCellValue('A1', 'Category');

        foreach ($report['months'] as $month => $label) {
            $column = Coordinate::stringFromColumnIndex($month + 1);
            $sheet->setCellValue($column . '1', $label);
            $sheet->setCellValue($column . '2', 'Amount');
        }

        $sheet->getStyle('A1:' . $lastColumn . '2')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::HEADER_FILL],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    /**
     * @param array<int, array{label: string, amounts: array<int, int>}> $rows
     */
    private function writeRows(Worksheet $sheet, array $rows, int $rowNumber, string $fill): int
    {
        foreach ($rows as $row) {
            $this->writeAmountRow($sheet, $row['label'], $row['amounts'], $rowNumber, $fill);
            $rowNumber++;
        }

        return $rowNumber;
    }

    /**
     * @param array<int, int> $amounts
     */
    private function writeAmountRow(
        Worksheet $sheet,
        string $label,
        array $amounts,
        int $rowNumber,
        ?string $fill = null
    ): void {
        $sheet->setCellValue('A' . $rowNumber, $label);

        foreach ($amounts as $month => $amount) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($month + 1) . $rowNumber, $amount);
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($amounts) + 1);
        $sheet->getStyle('B' . $rowNumber . ':' . $lastColumn . $rowNumber)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B' . $rowNumber . ':' . $lastColumn . $rowNumber)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        if ($fill === null) {
            return;
        }

        $sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $fill],
            ],
        ]);
    }
}
