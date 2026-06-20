<?php

namespace App\Http\Controllers;

use App\Contracts\ReportRepositoryInterface;
use App\Http\Requests\ReportYearRequest;
use App\Services\Reports\ReportWorkbookExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly ReportWorkbookExporter $exporter
    ) {
    }

    public function index(ReportYearRequest $request): View
    {
        $availableYears = $this->reports->availableYears();
        $selectedYear = $this->resolveYear($request->selectedYear(), $availableYears);

        return view('reports.index', [
            'availableYears' => $this->yearOptions($availableYears, $selectedYear),
            'report' => $this->reports->monthlyCategorySummary($selectedYear),
        ]);
    }

    public function export(ReportYearRequest $request): Response
    {
        $availableYears = $this->reports->availableYears();
        $selectedYear = $this->resolveYear($request->selectedYear(), $availableYears);
        $filename = sprintf('transaction-report-%d.xlsx', $selectedYear);

        return response($this->exporter->export($this->reports->monthlyCategorySummary($selectedYear)), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * @param array<int, int> $availableYears
     */
    private function resolveYear(?int $requestedYear, array $availableYears): int
    {
        if ($requestedYear !== null) {
            return $requestedYear;
        }

        return $availableYears[0] ?? (int) now()->format('Y');
    }

    /**
     * @param array<int, int> $availableYears
     *
     * @return array<int, int>
     */
    private function yearOptions(array $availableYears, int $selectedYear): array
    {
        if (! in_array($selectedYear, $availableYears, true)) {
            $availableYears[] = $selectedYear;
            rsort($availableYears);
        }

        return $availableYears;
    }
}
