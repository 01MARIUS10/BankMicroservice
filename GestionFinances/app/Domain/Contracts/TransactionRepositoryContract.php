<?php

namespace App\Domain\Contracts;

use App\Domain\TransactionStatus;
use App\Domain\TransactionType;
use App\Domain\TransactionValueObject;

interface TransactionRepositoryContract
{
    public function create(TransactionValueObject $transaction): TransactionValueObject;

    public function findById(string $id): ?TransactionValueObject;

    public function updateStatus(string $id, TransactionStatus $status): TransactionValueObject;

    /** @return TransactionValueObject[] */
    public function all(): array;
}
