<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function purchaseCoins(PurchaseRequest $request)
    {

        $user = $request->user();
        $value = $request->input('value');

        Log::info($user);
        Log::info($request->input('reference'));
        Log::info($value);
        Log::info($request->input('type'));
        try {
           
            $response = Http::post('https://dad-202425-payments-api.vercel.app/api/debit', [
                'type' => $request->input('type'),
                'reference' => $request->input('reference'),
                'value' => $value,
            ]);
            Log::info($response);
            if ($response->status() !== 201) {
                return response()->json([
                    'error' => 'Payment failed. Please check the details and try again.',
                    'details' => $response->json(),
                ], 422);
            }

            //Each euro is 10 braincoins
        
            $user->brain_coins_balance += $value*10;
            $user->save();
            Log::info('Transaction Data:', [
                'user_id' => $user->id,
                'type' => 'P', // P = Purchase
                'brain_coins' => $value*10,
                'transaction_datetime' => now(),
                'payment_type' => $request->input('type'),
                'payment_reference' => $request->input('reference'),
                'game_id' => null,
                'euros' => $value, 
                'custom' => null,  
            ]);
          
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'P', // P = Purchase
                'brain_coins' => $value*10,
                'transaction_datetime' => now(),
                'payment_type' => $request->input('type'),
                'payment_reference' => $request->input('reference'),
                'game_id' => null,
                'euros' => $value, 
                'custom' => null,  
            ]);

            return response()->json([
                'message' => 'Purchase successful!',
                'new_balance' => $user->brain_coins_balance,
            ], 201);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'error' => 'An unexpected error occurred during the purchase.',
            ], 500);
        }
    }
}
