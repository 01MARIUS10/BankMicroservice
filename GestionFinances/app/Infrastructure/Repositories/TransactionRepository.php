<?php

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Transaction;
use App\Domain\Contracts\TransactionRepositoryContract;
use App\Domain\TransactionValueObject;
use App\Domain\TransactionStatus;
use App\Domain\TransactionType;
use App\Infrastructure\Mappers\TransactionStatusMapper;
use App\Infrastructure\Mappers\TransactionTypeMapper;

final class TransactionRepository implements TransactionRepositoryContract
{
    public function __construct(
        private readonly TransactionStatusMapper $statusMapper,
        private readonly TransactionTypeMapper $typeMapper,
    ) {}
    public function create(TransactionValueObject $transaction): TransactionValueObject
    {
        // dd($transaction);
        $model = Transaction::create([
            'label' => $transaction->label,
            'amount' => $transaction->amount,
            'user_id' => $transaction->userId,
            'type' => $this->typeMapper->toDatabase($transaction->type),
            'status' => $this->statusMapper->toDatabase($transaction->status),
        ]);

        return $this->toValueObject($model);
    }

    public function findById(string $id): ?TransactionValueObject
    {
        $model = Transaction::find($id);

        return $model ? $this->toValueObject($model) : null;
    }

    public function updateStatus(string $id, TransactionStatus $status): TransactionValueObject
    {
        $model = Transaction::findOrFail($id);
        $model->update(['status' => $this->statusMapper->toDatabase($status)]);

        return $this->toValueObject($model->refresh());
    }

    /** @return TransactionValueObject[] */
    public function all(): array
    {
        return Transaction::all()
            ->map(fn (Transaction $model) => $this->toValueObject($model))
            ->all();
    }

    private function toValueObject(Transaction $model): TransactionValueObject
    {
        return new TransactionValueObject(
            id: $model->id,
            label: $model->label,
            amount: (float) $model->amount,
            userId: $model->user_id,
            type: $this->typeMapper->toDomain($model->type),
            status: $this->statusMapper->toDomain($model->status),
            createdAt: $model->created_at?->toISOString(),
            updatedAt: $model->updated_at?->toISOString(),
        );
    }
}
