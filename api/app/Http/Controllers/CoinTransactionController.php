<?php

namespace App\Http\Controllers;

use App\Models\CoinTransaction;
use Illuminate\Http\Request;

class CoinTransactionController extends Controller
{
    public function getUserTransactions(Request $request)
    {
        // Get the authenticated user
        $user = $request->user(); // works with Bearer token

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Eager-load coinTransactionType and paginate
        $transactions = CoinTransaction::with('coinTransactionType')
            ->where('user_id', $user->id)
            ->orderBy('transaction_datetime', 'desc')
            ->paginate(10);

        // Transform transactions safely
        $transactions->getCollection()->transform(function ($transaction) {
            $transaction->type_description = optional($transaction->coinTransactionType)->name ?? 'Unknown';
            $transaction->credit_or_debit = optional($transaction->coinTransactionType)->type ?? 'Unknown';
            return $transaction;
        });

        // Return JSON with pagination info
        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'transactions' => [
                'data' => $transactions->items(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ], 200);
    }
}
