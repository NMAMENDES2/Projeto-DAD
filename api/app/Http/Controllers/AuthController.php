<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * REGISTO (G1)
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'                  => $validated['name'],
            'email'                 => $validated['email'],
            'nickname'              => $validated['nickname'],
            'password'              => Hash::make($validated['password']),
            'type'                  => 'P',
            'blocked'               => false,
            'photo_avatar_filename' => null,
            'coins_balance'         => 0,
            'custom'                => null,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'ok',
            'token'   => $token,
            'user'    => new UserResource($user)
        ], 201);
    }

    /**
     * LOGIN (G1)
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 422);
        }

        $user = Auth::user();

        // Se estiver bloqueado → impedir login
        if ($user->blocked) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is blocked.'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => new UserResource($user)
        ], 200);
    }

    /**
     * LOGOUT (G1)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * AUTH / ME (G1)
     * devolve informação do utilizador autenticado
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
