<?php

namespace App\Infrastructure\Mappers;

use App\Domain\TransactionStatus;

class TransactionStatusMapper
{
    public function toDatabase(TransactionStatus $status): string
    {
        return match($status) {
            TransactionStatus::PENDING   => 'pending',
            TransactionStatus::COMPLETED => 'completed',
            TransactionStatus::CANCELLED => 'cancelled',
            TransactionStatus::FAILED    => 'failed',
        };
    }

    public function toDomain(string $value): TransactionStatus
    {
        return match($value) {
            'pending'   => TransactionStatus::PENDING,
            'completed' => TransactionStatus::COMPLETED,
            'cancelled' => TransactionStatus::CANCELLED,
            'failed'    => TransactionStatus::FAILED,
            default     => throw new \InvalidArgumentException(
                "Statut inconnu en base : {$value}"
            ),
        };
    }
}
