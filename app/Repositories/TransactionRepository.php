<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Models\Transaction;
use App\Models\User;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getTransactions(User $user, int $perPage = 15)
    {
        return Transaction::with('sender', 'receiver')
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }
}