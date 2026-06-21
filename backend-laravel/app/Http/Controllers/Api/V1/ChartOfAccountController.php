<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveChartOfAccountRequest;
use App\Http\Requests\SelectOptionsRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ChartOfAccountController extends Controller
{
    public function __construct(private readonly ChartOfAccountRepositoryInterface $accounts)
    {
    }

    public function index(): JsonResponse
    {
        return $this->accounts->dataTable()->toJson();
    }

    public function selectOptions(SelectOptionsRequest $request): JsonResponse
    {
        return response()->json($this->accounts->selectOptions(
            $request->searchTerm(),
            $request->page(),
            $request->perPage()
        ));
    }

    public function accountTypes(): JsonResponse
    {
        return response()->json([
            'data' => collect(ChartOfAccount::ACCOUNT_TYPE_LABELS)
                ->map(static fn (string $label, string $value): array => [
                    'value' => $value,
                    'label' => $label,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function store(SaveChartOfAccountRequest $request): JsonResponse
    {
        $account = ChartOfAccount::query()->create($request->validated());

        return (new ChartOfAccountResource($account->load('category')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ChartOfAccount $chartOfAccount): ChartOfAccountResource
    {
        return new ChartOfAccountResource($chartOfAccount->load('category'));
    }

    public function update(SaveChartOfAccountRequest $request, ChartOfAccount $chartOfAccount): JsonResponse
    {
        $chartOfAccount->update($request->validated());

        return (new ChartOfAccountResource($chartOfAccount->refresh()->load('category')))->response();
    }

    public function destroy(ChartOfAccount $chartOfAccount): JsonResponse|Response
    {
        try {
            $chartOfAccount->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This chart of account cannot be deleted because it is used by transactions.',
            ], Response::HTTP_CONFLICT);
        }

        return response()->noContent();
    }
}
