<?php

namespace Tests\Unit\Application\UpdateTransactionStatus;

use App\Application\UpdateTransactionStatus\UpdateTransactionStatusAction;
use App\Application\UpdateTransactionStatus\UpdateTransactionStatusInput;
use App\Domain\Contracts\TransactionRepositoryContract;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\TransactionStatus;
use App\Domain\TransactionType;
use App\Domain\TransactionValueObject;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UpdateTransactionStatusActionTest extends TestCase
{
    private TransactionRepositoryContract|Mockery\MockInterface $repository;
    private UpdateTransactionStatusAction $action;

    private const USER_ID = 'user-uuid-123';
    private const OTHER_USER_ID = 'user-uuid-999';
    private const TXN_ID = 'txn-uuid-1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TransactionRepositoryContract::class);
        $this->action = new UpdateTransactionStatusAction($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makePendingTransaction(): TransactionValueObject
    {
        return new TransactionValueObject(
            id: self::TXN_ID,
            label: 'Test',
            amount: 100.00,
            userId: self::USER_ID,
            type: TransactionType::INCOME,
            status: TransactionStatus::PENDING,
        );
    }

    // --- Transitions valides depuis PENDING ---

    public function test_it_transitions_from_pending_to_completed(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->with(self::TXN_ID)
            ->once()
            ->andReturn($this->makePendingTransaction());

        $expected = new TransactionValueObject(
            id: self::TXN_ID,
            label: 'Test',
            amount: 100.00,
            userId: self::USER_ID,
            type: TransactionType::INCOME,
            status: TransactionStatus::COMPLETED,
        );

        $this->repository
            ->shouldReceive('updateStatus')
            ->with(self::TXN_ID, TransactionStatus::COMPLETED)
            ->once()
            ->andReturn($expected);

        $result = $this->action->execute(new UpdateTransactionStatusInput(
            transactionId: self::TXN_ID,
            userId: self::USER_ID,
            status: TransactionStatus::COMPLETED,
        ));

        $this->assertSame(TransactionStatus::COMPLETED, $result->status);
    }

    public function test_it_transitions_from_pending_to_cancelled(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->with(self::TXN_ID)
            ->once()
            ->andReturn($this->makePendingTransaction());

        $expected = new TransactionValueObject(
            id: self::TXN_ID,
            label: 'Test',
            amount: 100.00,
            userId: self::USER_ID,
            type: TransactionType::INCOME,
            status: TransactionStatus::CANCELLED,
        );

        $this->repository
            ->shouldReceive('updateStatus')
            ->with(self::TXN_ID, TransactionStatus::CANCELLED)
            ->once()
            ->andReturn($expected);

        $result = $this->action->execute(new UpdateTransactionStatusInput(
            transactionId: self::TXN_ID,
            userId: self::USER_ID,
            status: TransactionStatus::CANCELLED,
        ));

        $this->assertSame(TransactionStatus::CANCELLED, $result->status);
    }

    public function test_it_transitions_from_pending_to_failed(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->with(self::TXN_ID)
            ->once()
            ->andReturn($this->makePendingTransaction());

        $expected = new TransactionValueObject(
            id: self::TXN_ID,
            label: 'Test',
            amount: 100.00,
            userId: self::USER_ID,
            type: TransactionType::INCOME,
            status: TransactionStatus::FAILED,
        );

        $this->repository
            ->shouldReceive('updateStatus')
            ->with(self::TXN_ID, TransactionStatus::FAILED)
            ->once()
            ->andReturn($expected);

        $result = $this->action->execute(new UpdateTransactionStatusInput(
            transactionId: self::TXN_ID,
            userId: self::USER_ID,
            status: TransactionStatus::FAILED,
        ));

        $this->assertSame(TransactionStatus::FAILED, $result->status);
    }

    // --- Transaction introuvable ---

    public function test_it_throws_not_found_when_transaction_does_not_exist(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->with('nonexistent-id')
            ->once()
            ->andReturnNull();

        $this->expectException(NotFoundException::class);

        $this->action->execute(new UpdateTransactionStatusInput(
            transactionId: 'nonexistent-id',
            userId: self::USER_ID,
            status: TransactionStatus::COMPLETED,
        ));
    }

    // --- Mauvais user ---

    public function test_it_throws_not_found_when_user_does_not_own_transaction(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->with(self::TXN_ID)
            ->once()
            ->andReturn($this->makePendingTransaction());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Transaction not found for this user.');

        $this->action->execute(new UpdateTransactionStatusInput(
            transactionId: self::TXN_ID,
            userId: self::OTHER_USER_ID,
            status: TransactionStatus::COMPLETED,
        ));
    }

    // --- Transitions invalides ---

    #[DataProvider('invalidTransitionsProvider')]
    public function test_it_throws_on_invalid_status_transition(
        TransactionStatus $from,
        TransactionStatus $to,
    ): void {
        $transaction = new TransactionValueObject(
            id: self::TXN_ID,
            label: 'Test',
            amount: 100.00,
            userId: self::USER_ID,
            type: TransactionType::INCOME,
            status: $from,
        );

        $this->repository
            ->shouldReceive('findById')
            ->with(self::TXN_ID)
            ->once()
            ->andReturn($transaction);

        $this->expectException(InvalidStatusTransitionException::class);

        $this->action->execute(new UpdateTransactionStatusInput(
            transactionId: self::TXN_ID,
            userId: self::USER_ID,
            status: $to,
        ));
    }

    public static function invalidTransitionsProvider(): array
    {
        return [
            'completed → pending'   => [TransactionStatus::COMPLETED, TransactionStatus::PENDING],
            'completed → cancelled' => [TransactionStatus::COMPLETED, TransactionStatus::CANCELLED],
            'completed → failed'    => [TransactionStatus::COMPLETED, TransactionStatus::FAILED],
            'cancelled → pending'   => [TransactionStatus::CANCELLED, TransactionStatus::PENDING],
            'cancelled → completed' => [TransactionStatus::CANCELLED, TransactionStatus::COMPLETED],
            'cancelled → failed'    => [TransactionStatus::CANCELLED, TransactionStatus::FAILED],
            'failed → pending'      => [TransactionStatus::FAILED, TransactionStatus::PENDING],
            'failed → completed'    => [TransactionStatus::FAILED, TransactionStatus::COMPLETED],
            'failed → cancelled'    => [TransactionStatus::FAILED, TransactionStatus::CANCELLED],
            'pending → pending'     => [TransactionStatus::PENDING, TransactionStatus::PENDING],
        ];
    }
}
