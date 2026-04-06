<?php

namespace App\Application\UpdateTransactionStatus;

use App\Domain\TransactionStatus;

final readonly class UpdateTransactionStatusInput
{
    public function __construct(
        public string $transactionId,
        public string $userId,
        public TransactionStatus $status,
    ) {}
}
