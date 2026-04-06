<?php

namespace App\Infrastructure\Mappers;

use App\Domain\TransactionStatus;
use App\Domain\TransactionType;

class TransactionTypeMapper
{
    public function toDatabase(TransactionType $type): string
    {
        return match($type) {
            TransactionType::INCOME  => 'income',
            TransactionType::OUTCOME => 'outcome',
             default => throw new \InvalidArgumentException(
                "Type de transaction inconnu : {$type->value}"
            ),
        };
    }

    public function toDomain(string $value): TransactionType
    {
        return match($value) {
            'income'   => TransactionType::INCOME,
            'outcome' => TransactionType::OUTCOME,
            default     => throw new \InvalidArgumentException(
                "Type de transaction inconnu en base : {$value}"
            ),
        };
    }
}
