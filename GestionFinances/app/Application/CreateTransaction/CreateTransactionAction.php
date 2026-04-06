<?php

namespace App\Application\CreateTransaction;

use App\Domain\Contracts\TransactionRepositoryContract;
use App\Domain\TransactionValueObject;

final class CreateTransactionAction
{
    public function __construct(
        private readonly TransactionRepositoryContract $repository,
    ) {}

    public function execute(CreateTransactionInput $input): TransactionValueObject
    {
        return $this->repository->create(
            new TransactionValueObject(
                id: null,
                label: $input->label,
                amount: $input->amount,
                userId: $input->userId,
                type: $input->type,
                status: $input->status,
            ),
        );
    }
}
