<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccountCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class ChartOfAccountCategoryController extends Controller
{
    public function __construct(private readonly ChartOfAccountCategoryRepositoryInterface $categories)
    {
    }

    public function index(): JsonResponse
    {
        return DataTables::eloquent($this->categories->tableQuery())
            ->only(['id', 'name', 'created_at', 'updated_at'])
            ->whitelist(['id', 'name', 'created_at', 'updated_at'])
            ->editColumn(
                'created_at',
                fn (ChartOfAccountCategory $category): ?string => $category->created_at?->toISOString()
            )
            ->editColumn(
                'updated_at',
                fn (ChartOfAccountCategory $category): ?string => $category->updated_at?->toISOString()
            )
            ->toJson();
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
