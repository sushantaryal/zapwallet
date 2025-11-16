<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {}
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userData = $this->transactionService->getUserTransactions($request->user());

        return response()->json([
            'balance' => $userData['balance'],
            'transactions' => $userData['transactions'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        $transaction = $this->transactionService->transfer(
            $request->user(),
            $request->input('receiver_id'),
            $request->input('amount')
        );

        return response()->json([
            'message' => 'Transaction successful',
            'transaction' => $transaction->load(['sender', 'receiver']),
        ]);
    }
}
