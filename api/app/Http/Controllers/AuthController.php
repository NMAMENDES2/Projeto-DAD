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
        try {
            $validated = $request->validated();

            $photoFilename = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoFilename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->move(storage_path('app/public/photos'), $photoFilename);
            }

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
                'message' => 'ok',
                'token' => $token,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * LOGIN (G1)
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => ['email' => ['Invalid email or password.']]
                ], 422);
            }

            $user = Auth::user();

            // Check if blocked
            if ($user->blocked) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked.'
                ], 403);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
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
