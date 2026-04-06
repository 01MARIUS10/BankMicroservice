<?php

namespace App\Presentation\Http\Controllers;

use App\Application\CreateTransaction\CreateTransactionAction;
use App\Application\CreateTransaction\CreateTransactionInput;
use App\Application\UpdateTransactionStatus\UpdateTransactionStatusAction;
use App\Application\UpdateTransactionStatus\UpdateTransactionStatusInput;

use App\Domain\TransactionStatus;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Contracts\TransactionRepositoryContract;
use App\Domain\TransactionType;

use App\Presentation\Http\Requests\CreateTransactionRequest;
use App\Presentation\Http\Requests\UpdateTransactionStatusRequest;
use App\Presentation\Http\Resources\TransactionResource;
use App\Presentation\Http\Responses\ApiResponse;

use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{

    public function __construct(
        private readonly TransactionRepositoryContract $repository,
        private readonly ApiResponse $response,
    ) {
    }

    public function index(): JsonResponse
    {

        try {
            $transactions = $this->repository->all();

            return $this->response->success(
                array_map(
                    fn ($t) => (new TransactionResource($t))->resolve()
                ,$transactions)
            );
        } catch (\Exception $e) {
            return $this->response->exceptionError($e);
        } catch (\Error $e) {
            return $this->response->systemError($e);
        }
    }

    public function store(
        CreateTransactionRequest $request,
        CreateTransactionAction $action,
    ): JsonResponse {

        try {
            $transaction = $action->execute(
                new CreateTransactionInput(
                    label: $request->validated('libelle'),
                    amount: $request->validated('montant'),
                    userId: $request->validated('user_id'),
                    type: TransactionType::from($request->validated('type')),
                )
            );

            return $this->response->created(
                (new TransactionResource($transaction))->resolve(),
                'Transaction created successfully.'
            );

        } catch (\Exception $e) {
            return $this->response->exceptionError($e);

        } catch (\Error $e) {
            return $this->response->systemError($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $transaction = $this->repository->findById($id);

            if (! $transaction) {
                return $this->response->notFound('Transaction not found.');
            }

            return $this->response->success(
                (new TransactionResource($transaction))->resolve()
            );

        } catch (\Exception $e) {
            return $this->response->exceptionError($e);

        } catch (\Error $e) {
            return $this->response->systemError($e);
        }
    }

    public function updateStatus(
        string $id,
        UpdateTransactionStatusRequest $request,
        UpdateTransactionStatusAction $action,
    ): JsonResponse {
        try {
            $transaction = $action->execute(
                new UpdateTransactionStatusInput(
                    transactionId: $id,
                    userId: $request->validated('user_id'),
                    status: TransactionStatus::from($request->validated('status')),
                )
            );

            return $this->response->success(
                (new TransactionResource($transaction))->resolve()
            );

        } catch (NotFoundException $e) {
            return $this->response->notFound($e->getMessage());

        } catch (\Exception $e) {
            return $this->response->exceptionError($e);

        } catch (\Error $e) {
            return $this->response->systemError($e);
        }
    }
}
