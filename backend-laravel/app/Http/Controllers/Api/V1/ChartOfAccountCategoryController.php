<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChartOfAccountCategoryResource;
use App\Models\ChartOfAccountCategory;
use App\Support\DataTables\DataTableQuery;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ChartOfAccountCategoryController extends Controller
{
    public function __construct(private readonly ChartOfAccountCategoryRepositoryInterface $categories)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return ChartOfAccountCategoryResource::collection(
            $this->categories->paginate(DataTableQuery::fromRequest($request))
        );
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
