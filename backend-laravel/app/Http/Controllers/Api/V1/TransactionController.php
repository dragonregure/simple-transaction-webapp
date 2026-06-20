<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TransactionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionRepositoryInterface $transactions)
    {
    }

    public function index(): JsonResponse
    {
        return $this->transactions->dataTable()->toJson();
    }

    public function destroy(Transaction $transaction): Response
    {
        $transaction->delete();

        return response()->noContent();
    }
}
