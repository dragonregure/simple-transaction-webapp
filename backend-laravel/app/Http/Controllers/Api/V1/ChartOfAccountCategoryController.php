<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountCategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccountCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function selectOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'term' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json($this->categories->selectOptions(
            (string) ($validated['term'] ?? $validated['q'] ?? ''),
            (int) ($validated['page'] ?? 1),
            (int) ($validated['per_page'] ?? 20)
        ));
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
