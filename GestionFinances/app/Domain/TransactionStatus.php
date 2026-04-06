<?php

namespace App\Domain;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::PENDING => in_array($new, [self::COMPLETED, self::CANCELLED, self::FAILED]),
            self::COMPLETED, self::CANCELLED, self::FAILED => false,
        };
    }
}
