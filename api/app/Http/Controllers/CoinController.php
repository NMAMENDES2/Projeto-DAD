<?php
    
namespace App\Http\Controllers;

use App\Http\Requests\PurchaseCoinsRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\CoinTransaction;
use App\Models\CoinTransactionType;
use App\Models\CoinPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CoinController extends Controller
{
    // GET /api/coins/balance
    public function getBalance(Request $request)
    {
        return response()->json([
            'balance' => $request->user()->braincoinsbalance
        ]);
    }

    // POST /api/coins/purchase
    public function purchaseCoins(PurchaseRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $euros = $validated['euros'];
        $coins = $euros * 10;

        // Chamar API externa de pagamentos
        try {
            $response = Http::post('https://dad-payments-api.vercel.app/api/debit', [
                'type' => $validated['payment_type'],
                'reference' => $validated['payment_reference'],
                'value' => $euros,
            ]);

            if ($response->status() !== 201) {
                return response()->json([
                    'message' => 'Falha no pagamento.',
                    'error' => $response->json()
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao contactar serviço de pagamentos.'
            ], 500);
        }

        // Criar transação
        DB::beginTransaction();                           
        try {
            $transactionType = CoinTransactionType::where('name', 'Coin purchase')->first();

            $coinTransaction = CoinTransaction::create([
                'user_id' => $user->id,
                'coin_transaction_type_id' => $transactionType->id,
                'coins' => $coins,
                'transaction_datetime' => now(),
            ]);

            CoinPurchase::create([
                'user_id' => $user->id,
                'coin_transaction_id' => $coinTransaction->id,
                'euros' => $euros,
                'payment_type' => $validated['payment_type'],
                'payment_reference' => $validated['payment_reference'],
                'purchase_datetime' => now(),
            ]);

            $user->increment('braincoinsbalance', $coins);

            DB::commit();

            return response()->json([
                'message' => 'Compra realizada com sucesso!',
                'balance' => $user->braincoinsbalance,
                'coins_purchased' => $coins,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao processar compra.'], 500);
        }
    }

    // GET /api/coins/transactions (próprio utilizador)
    public function getTransactions(Request $request)
    {
        $transactions = CoinTransaction::where('user_id', $request->user()->id)
            ->with('coinTransactionType')
            ->orderBy('transaction_datetime', 'desc')
            ->get();

        return response()->json(['transactions' => $transactions]);
    }

    // GET /api/admin/coins/transactions (admin - todos)
    public function getAllTransactions(Request $request)
    {
        if ($request->user()->type !== 'Admin') {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $transactions = CoinTransaction::with(['user', 'coinTransactionType'])
            ->orderBy('transaction_datetime', 'desc')
            ->get();

        return response()->json(['transactions' => $transactions]);
    }

    // GET /api/admin/users/{UserId}/transactions
    public function getUserTransactions(Request $request, $userId)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $transactions = CoinTransaction::where('user_id', $userId)
            ->with('coinTransactionType')
            ->orderBy('transaction_datetime', 'desc')
            ->get();

        return response()->json(['transactions' => $transactions]);
    }
}