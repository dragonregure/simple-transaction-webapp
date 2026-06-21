<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveChartOfAccountCategoryRequest;
use App\Http\Requests\SelectOptionsRequest;
use App\Http\Resources\ChartOfAccountCategoryResource;
use App\Models\ChartOfAccountCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ChartOfAccountCategoryController extends Controller
{
    public function __construct(private readonly ChartOfAccountCategoryRepositoryInterface $categories)
    {
    }

    public function index(): JsonResponse
    {
        return $this->categories->dataTable()->toJson();
    }

    public function selectOptions(SelectOptionsRequest $request): JsonResponse
    {
        return response()->json($this->categories->selectOptions(
            $request->searchTerm(),
            $request->page(),
            $request->perPage()
        ));
    }

    public function store(SaveChartOfAccountCategoryRequest $request): JsonResponse
    {
        $category = ChartOfAccountCategory::query()->create($request->validated());

        return (new ChartOfAccountCategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ChartOfAccountCategory $chartOfAccountCategory): ChartOfAccountCategoryResource
    {
        return new ChartOfAccountCategoryResource($chartOfAccountCategory);
    }

    public function update(
        SaveChartOfAccountCategoryRequest $request,
        ChartOfAccountCategory $chartOfAccountCategory
    ): JsonResponse {
        $chartOfAccountCategory->update($request->validated());

        return (new ChartOfAccountCategoryResource($chartOfAccountCategory->refresh()))->response();
    }

    public function destroy(ChartOfAccountCategory $chartOfAccountCategory): JsonResponse|Response
    {
        try {
            $chartOfAccountCategory->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'This category cannot be deleted because it is used by chart of accounts.',
            ], Response::HTTP_CONFLICT);
        }

        return response()->noContent();
    }
}
