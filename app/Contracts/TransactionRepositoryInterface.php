<?php

namespace App\Contracts;

use App\Models\Transaction;
use App\Models\User;

interface TransactionRepositoryInterface
{
    public function getTransactions(User $user, int $perPage = 15);

    public function createTransaction(array $data): Transaction;
}