<?php

namespace App\Domain;

use App\Domain\TransactionStatus;
use App\Domain\TransactionType;

final readonly class TransactionValueObject
{
    public function __construct(
        public ?string $id,
        public string $label,
        public float $amount,
        public string $userId,
        public TransactionType $type,
        public TransactionStatus $status,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    public function isForUser(string $userId): bool
    {
        return $this->userId === $userId;
    }
}
