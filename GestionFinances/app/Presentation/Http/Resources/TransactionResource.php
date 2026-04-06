<?php

namespace App\Presentation\Http\Resources;

use App\Domain\TransactionValueObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function __construct(private readonly TransactionValueObject $transaction)
    {
        parent::__construct($transaction);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->transaction->id,
            'label' => $this->transaction->label,
            'amount' => $this->transaction->amount,
            'user_id' => $this->transaction->userId,
            'type' => $this->transaction->type,
            'status' => $this->transaction->status->value,
            'created_at' => $this->transaction->createdAt,
            'updated_at' => $this->transaction->updatedAt,
        ];
    }
}
