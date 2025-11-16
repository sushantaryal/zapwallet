<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $sender;
    protected $receiver;

    public function setUp(): void
    {
        parent::setUp();

        $this->sender = User::factory()->create(['balance' => 1000.00]);
        $this->receiver = User::factory()->create(['balance' => 500.00]);
    }
    
    public function test_user_can_fetch_balance_and_transactions(): void
    {
        $this->actingAs($this->sender, 'sanctum');

        $response = $this->get('/api/transactions');

        $response->assertOk(200)
            ->assertJsonStructure(['balance', 'transactions']);
    }

    public function test_user_can_transfer_money_and_create_transaction(): void
    {
        $this->actingAs($this->sender, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $this->receiver->id,
            'amount' => 100.00,
        ]);

        $response->assertOk(200);

        $this->assertEquals(898.50, $this->sender->refresh()->balance);
        $this->assertEquals(600.00, $this->receiver->refresh()->balance);

        $this->assertDatabaseHas('transactions', [
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'amount' => 100.00,
            'commission_fee' => 1.50,
        ]);
    }

    public function test_user_cannot_transfer_more_than_their_balance(): void
    {
        Exceptions::fake();

        $this->actingAs($this->sender, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $this->receiver->id,
            'amount' => 1000.00,
        ]);

        $response->assertStatus(500);

        Exceptions::assertReported(function (\Exception $e) {
            return $e->getMessage() === 'Insufficient funds';
        });
    }

    public function test_atomic_transaction_and_rollback_on_failure(): void
    {
        $this->actingAs($this->sender, 'sanctum');

        $this->mock(\App\Services\TransactionService::class, function ($mock) {
            $mock->shouldReceive('transfer')
                ->andThrow(new \Exception('Simulated failure'));
        });

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $this->receiver->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Simulated failure',
            ]);

        $this->assertEquals(1000.00, $this->sender->refresh()->balance);
        $this->assertEquals(500.00, $this->receiver->refresh()->balance);
        
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_user_cannot_transfer_to_nonexistent_user(): void
    {
        $this->actingAs($this->sender, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => 9999,
            'amount' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receiver_id']);
    }

    public function test_user_cannot_transfer_to_themselves(): void
    {
        $this->actingAs($this->sender, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'receiver_id' => $this->sender->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
