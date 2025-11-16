<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(private TransactionRepositoryInterface $transactions) {}

    /**
     * Transfer amount from sender to receiver
     */
    public function transfer(User $sender, int $receiverId, float $amount)
    {
        if ($sender->id === $receiverId) {
            throw ValidationException::withMessages([
                'amount' => ['The amount cannot be transferred to yourself'],
            ]);
        }

        return DB::transaction(function () use ($sender, $receiverId, $amount) {
            $receiver = User::lockForUpdate()->findOrFail($receiverId);
            $sender = User::lockForUpdate()->findOrFail($sender->id);

            $amount = round($amount, 2);
            $commission = round($amount * 0.015, 2);
            $totalDeduction = round($amount + $commission, 2);

            if ($sender->balance < $totalDeduction) {
                throw new \Exception('Insufficient funds');
            }

            $sender->balance -= $totalDeduction;
            $receiver->balance += $amount;

            $sender->save();
            $receiver->save();

            $transaction = $this->transactions->createTransaction([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commission,
            ]);

            TransactionCreated::dispatch($transaction);

            return $transaction;
        });
    }

    /**
     * Get user transactions along with current balance
     */
    public function getUserTransactions(User $user)
    {
        $transactions = $this->transactions->getTransactions($user);
        return [
            'balance' => $user->balance,
            'transactions' => $transactions,
        ];
    }
}