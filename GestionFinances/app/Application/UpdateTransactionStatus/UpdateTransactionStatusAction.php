<?php

namespace App\Application\UpdateTransactionStatus;

use App\Domain\Contracts\TransactionRepositoryContract;
use App\Domain\TransactionValueObject;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\Exceptions\NotFoundException;

final class UpdateTransactionStatusAction
{
    public function __construct(
        private readonly TransactionRepositoryContract $repository,
    ) {}

    public function execute(UpdateTransactionStatusInput $input): TransactionValueObject
    {
        $transaction = $this->repository->findById($input->transactionId);

        if (! $transaction) {
            throw new NotFoundException('Transaction not found.');
        }
        if(! $transaction->isForUser($input->userId) ) {
            throw new NotFoundException('Transaction not found for this user.');
        }

        if (! $transaction->status->canTransitionTo($input->status)) {
            throw new InvalidStatusTransitionException(
                $transaction->status->value,
                $input->status->value,
            );
        }

        return $this->repository->updateStatus($input->transactionId, $input->status);
    }
}
