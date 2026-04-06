<?php

namespace App\Application\CreateTransaction;

use App\Domain\TransactionStatus;
use App\Domain\TransactionType;

final readonly class CreateTransactionInput
{
    public function __construct(
        public string $label,
        public float $amount,
        public string $userId,
        public TransactionType $type,
        public TransactionStatus $status = TransactionStatus::PENDING,
    ) {}
}
