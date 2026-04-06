<?php

namespace Tests\Unit\Application\CreateTransaction;

use App\Application\CreateTransaction\CreateTransactionAction;
use App\Application\CreateTransaction\CreateTransactionInput;
use App\Domain\Contracts\TransactionRepositoryContract;
use App\Domain\TransactionStatus;
use App\Domain\TransactionType;
use App\Domain\TransactionValueObject;
use Mockery;
use PHPUnit\Framework\TestCase;

class CreateTransactionActionTest extends TestCase
{
    private TransactionRepositoryContract|Mockery\MockInterface $repository;
    private CreateTransactionAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(TransactionRepositoryContract::class);
        $this->action = new CreateTransactionAction($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_creates_a_transaction_with_default_pending_status(): void
    {
        $input = new CreateTransactionInput(
            label: 'Salaire',
            amount: 3500.00,
            userId: 'user-uuid-123',
            type: TransactionType::INCOME,
        );

        $expected = new TransactionValueObject(
            id: 'txn-uuid-1',
            label: 'Salaire',
            amount: 3500.00,
            userId: 'user-uuid-123',
            type: TransactionType::INCOME,
            status: TransactionStatus::PENDING,
            createdAt: '2026-04-06T00:00:00.000000Z',
            updatedAt: '2026-04-06T00:00:00.000000Z',
        );

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (TransactionValueObject $vo) {
                return $vo->id === null
                    && $vo->label === 'Salaire'
                    && $vo->amount === 3500.00
                    && $vo->userId === 'user-uuid-123'
                    && $vo->type === TransactionType::INCOME
                    && $vo->status === TransactionStatus::PENDING;
            }))
            ->andReturn($expected);

        $result = $this->action->execute($input);

        $this->assertSame('txn-uuid-1', $result->id);
        $this->assertSame('Salaire', $result->label);
        $this->assertSame(3500.00, $result->amount);
        $this->assertSame('user-uuid-123', $result->userId);
        $this->assertSame(TransactionType::INCOME, $result->type);
        $this->assertSame(TransactionStatus::PENDING, $result->status);
    }

    public function test_it_creates_an_outcome_transaction(): void
    {
        $input = new CreateTransactionInput(
            label: 'Loyer',
            amount: 900.00,
            userId: 'user-uuid-456',
            type: TransactionType::OUTCOME,
        );

        $expected = new TransactionValueObject(
            id: 'txn-uuid-2',
            label: 'Loyer',
            amount: 900.00,
            userId: 'user-uuid-456',
            type: TransactionType::OUTCOME,
            status: TransactionStatus::PENDING,
        );

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expected);

        $result = $this->action->execute($input);

        $this->assertSame(TransactionType::OUTCOME, $result->type);
        $this->assertSame(TransactionStatus::PENDING, $result->status);
    }

    public function test_it_passes_null_id_to_repository(): void
    {
        $input = new CreateTransactionInput(
            label: 'Test',
            amount: 100.00,
            userId: 'user-uuid-789',
            type: TransactionType::INCOME,
        );

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (TransactionValueObject $vo) => $vo->id === null))
            ->andReturn(new TransactionValueObject(
                id: 'txn-uuid-3',
                label: 'Test',
                amount: 100.00,
                userId: 'user-uuid-789',
                type: TransactionType::INCOME,
                status: TransactionStatus::PENDING,
            ));

        $result = $this->action->execute($input);

        $this->assertNotNull($result->id);
    }

    public function test_default_status_is_pending(): void
    {
        $input = new CreateTransactionInput(
            label: 'Test default',
            amount: 50.00,
            userId: 'user-uuid-000',
            type: TransactionType::INCOME,
        );

        $this->assertSame(TransactionStatus::PENDING, $input->status);
    }
}
