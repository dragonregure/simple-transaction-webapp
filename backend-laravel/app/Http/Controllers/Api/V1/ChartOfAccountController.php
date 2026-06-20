<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ChartOfAccountRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function selectOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'term' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json($this->accounts->selectOptions(
            (string) ($validated['term'] ?? $validated['q'] ?? ''),
            (int) ($validated['page'] ?? 1),
            (int) ($validated['per_page'] ?? 20)
        ));
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
