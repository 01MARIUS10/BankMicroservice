<?php

namespace App\Infrastructure\Mappers;

use App\Domain\TransactionStatus;

class TransactionStatusMapper
{
    public function toDatabase(TransactionStatus $status): string
    {
        return match($status) {
            TransactionStatus::PENDING   => 'PENDING',
            TransactionStatus::SUCCESS => 'SUCCESS',
            TransactionStatus::FAILED    => 'FAILED',
        };
    }

    public function toDomain(string $value): TransactionStatus
    {
        return match($value) {
            'PENDING'   => TransactionStatus::PENDING,
            'SUCCESS' => TransactionStatus::SUCCESS,
            'FAILED'    => TransactionStatus::FAILED,
            default     => throw new \InvalidArgumentException(
                "Statut inconnu en base : {$value}"
            ),
        };
    }
}
