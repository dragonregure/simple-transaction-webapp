<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ReportRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportYearRequest;
use App\Services\Reports\ReportWorkbookExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly ReportWorkbookExporter $exporter
    ) {
    }

    public function index(ReportYearRequest $request): JsonResponse
    {
        $availableYears = $this->reports->availableYears();
        $selectedYear = $this->resolveYear($request->selectedYear(), $availableYears);

        return response()->json([
            'data' => [
                'available_years' => $this->yearOptions($availableYears, $selectedYear),
                'selected_year' => $selectedYear,
                'report' => $this->reports->monthlyCategorySummary($selectedYear),
            ],
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
