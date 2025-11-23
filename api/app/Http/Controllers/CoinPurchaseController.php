<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinPurchaseRequest;
use App\Models\CoinPurchase;
use App\Models\CoinTransaction;
use App\Models\CoinTransactionType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

class CoinPurchaseController extends Controller
{
    public function purchaseCoins(CoinPurchaseRequest $request)
{
    $user = $request->user();
    $value = $request->input('value'); // valor em euros
    $paymentType = $request->input('type');
    $paymentReference = $request->input('reference');

    try {
        // 1️⃣ Validar pagamento externo
        $response = Http::post('https://dad-payments-api.vercel.app/api/debit', [
            'type' => $paymentType,
            'reference' => $paymentReference,
            'value' => $value,
        ]);

        error_log($response);

        if ($response->status() !== 201) {
            return response()->json([
                'error' => 'Payment failed. Please check the details and try again.',
                'details' => $response->json(),
            ], 422);
        }

        // 2️⃣ Atualizar saldo do usuário
        $coinsToAdd = $value * 10; // Cada euro = 10 braincoins
        $user->coins_balance += $coinsToAdd;
        $user->save();

        // 3️⃣ Criar transação
        $purchaseType = CoinTransactionType::where('name', 'Coin purchase')->firstOrFail();

        $transaction = CoinTransaction::create([
            'user_id' => $user->id,
            'transaction_datetime' => now(),
            'coin_transaction_type_id' => $purchaseType->id,
            'coins' => $coinsToAdd,
            'game_id' => null,
            'match_id' => null,
            'custom' => null,
        ]);

        // 4️⃣ Criar registro de compra vinculado à transação
        CoinPurchase::create([
            'user_id' => $user->id,
            'coin_transaction_id' => $transaction->id,
            'purchase_datetime' => now(),
            'payment_type' => $paymentType,
            'payment_reference' => $paymentReference,
            'euros' => $value,
            'custom' => null,
        ]);

        // 5️⃣ Retornar dados para frontend
        return response()->json([
            'message' => 'Purchase successful!',
            'new_balance' => $user->brain_coins_balance,
            'transaction' => $transaction,
        ], 201);

    } catch (\Exception $e) {
        error_log($e->getMessage());
        Log::error('Coin purchase error: '.$e->getMessage());
        return response()->json([
            'error' => 'An unexpected error occurred during the purchase.',
        ], 500);
    }
}

}
