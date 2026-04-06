<?php

namespace Database\Seeders;

use App\Application\CreateTransaction\CreateTransactionAction;
use App\Application\CreateTransaction\CreateTransactionInput;
use App\Application\UpdateTransactionStatus\UpdateTransactionStatusAction;
use App\Application\UpdateTransactionStatus\UpdateTransactionStatusInput;
use App\Domain\TransactionStatus;
use App\Domain\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $createAction = app(CreateTransactionAction::class);
        // $updateStatusAction = app(UpdateTransactionStatusAction::class);

        $userId = "550e8400-e29b-41d4-a716-446655440000"; // Example user ID
        $transactions = [
            ['user_id'=>$userId,'label' => 'Salaire mensuel',        'amount' => 3500.00, 'type' => 'income',  'status' => 'pending'],
            ['user_id'=>$userId,'label' => 'Prime annuelle',          'amount' => 1200.00, 'type' => 'income',  'status' => 'completed'],
            ['user_id'=>$userId,'label' => 'Remboursement annulé',    'amount' => 150.00,  'type' => 'income',  'status' => 'cancelled'],
            ['user_id'=>$userId,'label' => 'Virement échoué',         'amount' => 500.00,  'type' => 'income',  'status' => 'failed'],
            ['user_id'=>$userId,'label' => 'Loyer en attente',        'amount' => 900.00,  'type' => 'outcome', 'status' => 'pending'],
            ['user_id'=>$userId,'label' => 'Facture électricité',     'amount' => 85.50,   'type' => 'outcome', 'status' => 'completed'],
            ['user_id'=>$userId,'label' => 'Abonnement annulé',       'amount' => 29.99,   'type' => 'outcome', 'status' => 'cancelled'],
            ['user_id'=>$userId,'label' => 'Paiement refusé',         'amount' => 200.00,  'type' => 'outcome', 'status' => 'failed'],
        ];

        foreach ($transactions as $data) {
            $transaction = $createAction->execute(
                new CreateTransactionInput(
                    label: $data['label'],
                    amount: $data['amount'],
                    userId: $data['user_id'],
                    type: TransactionType::from($data['type']),
                )
            );

            // if ($data['status'] !== 'pending') {
            //     $updateStatusAction->execute(
            //         new UpdateTransactionStatusInput(
            //             transactionId: $transaction->id,
            //             status: TransactionStatus::from($data['status']),
            //         )
            //     );
            // }
        }
    }
}
