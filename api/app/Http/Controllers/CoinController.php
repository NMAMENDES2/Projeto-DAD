<?php
    
namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Transaction;
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
    $request->validate(Transaction::rules('purchase'));

    $user = $request->user();
    $euros = $request->euros;
    $coins = $euros * 10;

    // Chamar API externa
    try {
        $response = Http::post('https://dad-payments-api.vercel.app/api/debit', [
            'type' => $request->payment_type,
            'reference' => $request->payment_reference,
            'value' => $euros,
        ]);

        if ($response->status() !== 201) {
            return response()->json(['message' => 'Falha no pagamento'], 422);
        }
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erro no serviço de pagamentos'], 500);
    }

        // Criar transação
        DB::beginTransaction();
    try {
        Transaction::create([
            'type' => 'purchase',
            'user_id' => $user->id,
            'euros' => $euros,
            'payment_type' => $request->payment_type,
            'payment_reference' => $request->payment_reference,
            'brain_coins' => $coins,
            'transaction_datetime' => now(),
        ]);

        $user->increment('braincoinsbalance', $coins);
        DB::commit();

        return response()->json([
            'message' => 'Compra realizada!',
            'balance' => $user->braincoinsbalance,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Erro ao processar'], 500);
    }
    }   

    // GET /api/coins/transactions (próprio utilizador)
    public function getTransactions(Request $request)
{
    $transactions = Transaction::where('user_id', $request->user()->id)
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

        $transactions = Transaction::with(['user', 'coinTransactionType'])
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

        $transactions = Transaction::where('user_id', $userId)
            ->with('coinTransactionType')
            ->orderBy('transaction_datetime', 'desc')
            ->get();

        return response()->json(['transactions' => $transactions]);
    }
}