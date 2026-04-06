<?php

namespace App\Domain;

enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';

    public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::PENDING => in_array($new, [self::SUCCESS, self::FAILED]),
            self::SUCCESS, self::FAILED => false,
        };
    }
}
