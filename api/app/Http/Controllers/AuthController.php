<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * REGISTO (G1)
     * Cria novo player com 10 brain coins
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $photoFilename = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoFilename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(storage_path('app/public/photos'), $photoFilename);
        }

        // Criar utilizador com 10 brain coins (G1)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nickname' => $validated['nickname'],
            'password' => Hash::make($validated['password']),
            'type' => 'P',
            'brain_coins_balance' => 10,
            'photo_filename' => $photoFilename,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
    }

    /**
     * LOGIN (G1)
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Verificar se está bloqueado (G1)
        if ($user->blocked) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been blocked.'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * LOGOUT (G1)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * REFRESH TOKEN
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return response()->json([
            'token' => $token
        ]);
    }
}
