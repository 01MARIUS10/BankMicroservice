<?php

namespace Tests\Feature;

use App\Infrastructure\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionEndpointTest extends TestCase
{
    use RefreshDatabase;

    private const USER_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const OTHER_USER_ID = '550e8400-e29b-41d4-a716-446655440099';

    // ──────────────────────────────────────
    //  STORE  POST /api/transactions
    // ──────────────────────────────────────

    public function test_store_creates_a_transaction(): void
    {
        $response = $this->postJson('/api/transactions', [
            'libelle' => 'Salaire',
            'montant' => 3500.00,
            'type' => 'income',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.label', 'Salaire')
            ->assertJsonPath('data.amount', 3500)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.status', 'PENDING');

        $this->assertDatabaseHas('transactions', [
            'label' => 'Salaire',
            'amount' => '3500.00',
            'type' => 'income',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);
    }

    public function test_store_creates_an_outcome_transaction(): void
    {
        $response = $this->postJson('/api/transactions', [
            'libelle' => 'Loyer',
            'montant' => 900.00,
            'type' => 'outcome',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'outcome')
            ->assertJsonPath('data.status', 'PENDING');
    }

    public function test_store_returns_401_without_user_id_header(): void
    {
        $response = $this->postJson('/api/transactions', [
            'libelle' => 'Salaire',
            'montant' => 3500.00,
            'type' => 'income',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'unauthorized');
    }

    public function test_store_returns_validation_error_when_libelle_missing(): void
    {
        $response = $this->postJson('/api/transactions', [
            'montant' => 100.00,
            'type' => 'income',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(400)
            ->assertJsonPath('status', 'validation_error');
    }

    public function test_store_returns_validation_error_when_montant_missing(): void
    {
        $response = $this->postJson('/api/transactions', [
            'libelle' => 'Test',
            'type' => 'income',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(400)
            ->assertJsonPath('status', 'validation_error');
    }

    public function test_store_returns_validation_error_when_type_invalid(): void
    {
        $response = $this->postJson('/api/transactions', [
            'libelle' => 'Test',
            'montant' => 100.00,
            'type' => 'invalid',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(400)
            ->assertJsonPath('status', 'validation_error');
    }

    public function test_store_returns_validation_error_when_montant_is_zero(): void
    {
        $response = $this->postJson('/api/transactions', [
            'libelle' => 'Test',
            'montant' => 0,
            'type' => 'income',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(400)
            ->assertJsonPath('status', 'validation_error');
    }

    // ──────────────────────────────────────
    //  SHOW  GET /api/transactions/{id}
    // ──────────────────────────────────────

    public function test_show_returns_a_transaction(): void
    {
        $txn = Transaction::create([
            'label' => 'Facture',
            'amount' => 85.50,
            'type' => 'outcome',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->getJson("/api/transactions/{$txn->id}", [
            'X-User-Id' => self::USER_ID,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $txn->id)
            ->assertJsonPath('data.label', 'Facture')
            ->assertJsonPath('data.amount', 85.5)
            ->assertJsonPath('data.type', 'outcome')
            ->assertJsonPath('data.status', 'PENDING');
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/transactions/{$fakeId}", [
            'X-User-Id' => self::USER_ID,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('status', 'not_found');
    }

    // ──────────────────────────────────────
    //  UPDATE STATUS  PATCH /api/transactions/{id}
    // ──────────────────────────────────────

    public function test_update_status_from_pending_to_completed(): void
    {
        $txn = Transaction::create([
            'label' => 'Salaire',
            'amount' => 3500.00,
            'type' => 'income',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->patchJson("/api/transactions/{$txn->id}", [
            'status' => 'SUCCESS',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'SUCCESS');

        $this->assertDatabaseHas('transactions', [
            'id' => $txn->id,
            'status' => 'SUCCESS',
        ]);
    }


    public function test_update_status_from_pending_to_failed(): void
    {
        $txn = Transaction::create([
            'label' => 'Test',
            'amount' => 100.00,
            'type' => 'income',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->patchJson("/api/transactions/{$txn->id}", [
            'status' => 'FAILED',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'FAILED');
    }

    public function test_update_status_returns_401_without_user_id_header(): void
    {
        $txn = Transaction::create([
            'label' => 'Test',
            'amount' => 100.00,
            'type' => 'income',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->patchJson("/api/transactions/{$txn->id}", [
            'status' => 'SUCCESS',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'unauthorized');
    }

    public function test_update_status_returns_404_when_transaction_not_found(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->patchJson("/api/transactions/{$fakeId}", [
            'status' => 'SUCCESS',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(404)
            ->assertJsonPath('status', 'not_found');
    }

    public function test_update_status_returns_404_when_user_does_not_own_transaction(): void
    {
        $txn = Transaction::create([
            'label' => 'Test',
            'amount' => 100.00,
            'type' => 'income',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->patchJson("/api/transactions/{$txn->id}", [
            'status' => 'SUCCESS',
        ], ['X-User-Id' => self::OTHER_USER_ID]);

        $response->assertStatus(404)
            ->assertJsonPath('status', 'not_found');
    }

    public function test_update_status_rejects_invalid_transition_from_completed(): void
    {
        $txn = Transaction::create([
            'label' => 'Test',
            'amount' => 100.00,
            'type' => 'income',
            'status' => 'SUCCESS',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->patchJson("/api/transactions/{$txn->id}", [
            'status' => 'PENDING',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(400);
    }

    public function test_update_status_returns_validation_error_with_invalid_status(): void
    {
        $txn = Transaction::create([
            'label' => 'Test',
            'amount' => 100.00,
            'type' => 'income',
            'status' => 'PENDING',
            'user_id' => self::USER_ID,
        ]);

        $response = $this->patchJson("/api/transactions/{$txn->id}", [
            'status' => 'invalid_status',
        ], ['X-User-Id' => self::USER_ID]);

        $response->assertStatus(400)
            ->assertJsonPath('status', 'validation_error');
    }
}
