<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TransactionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionRepositoryInterface $transactions)
    {
    }

    public function index(): JsonResponse
    {
        return $this->transactions->dataTable()->toJson();
    }

    public function store(SaveTransactionRequest $request): JsonResponse
    {
        $attributes = $request->transactionAttributes();

        try {
            $transaction = DB::transaction(
                static fn (): Transaction => Transaction::query()->create($attributes)
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to save transaction. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new TransactionResource($transaction->load('chartOfAccount')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    public function update(SaveTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $attributes = $request->transactionAttributes();

        try {
            DB::transaction(static function () use ($transaction, $attributes): void {
                $transaction->update($attributes);
            });
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to save transaction. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return (new TransactionResource($transaction->refresh()->load('chartOfAccount')))->response();
    }

    public function destroy(Transaction $transaction): Response
    {
        $transaction->delete();

        return response()->noContent();
    }
}
