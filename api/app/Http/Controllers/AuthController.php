<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * REGISTO (G1)
     */
    public function register(RegisterRequest $request){
        $user = User::create([
            'name'                  => $request['name'],
            'email'                 => $request['email'],
            'nickname'              => $request['nickname'],
            'password'              => Hash::make($request['password']),
            'type'                  => 'P',
            'blocked'               => false,
            'photo_avatar_filename' => null,
            'coins_balance'         => 10,
            'custom'                => null,
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'user'    => new UserResource($user)
        ], 201);
    }

    /**
     * LOGIN (G1)
     */
     public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * LOGOUT (G1)
     */
     public function logout(Request $request)
    {
        $this->purgeExpiredTokens();
        $this->revokeCurrentToken($request->user());
        return response()->json(null, 204);
    }

    /**
     * AUTH / ME (G1)
     * devolve informação do utilizador autenticado
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function refreshToken(Request $request)
    {
        // Revokes current token and creates a new token
        $this->purgeExpiredTokens();
        $this->revokeCurrentToken($request->user());
        $token = $request->user()->createToken('authToken', ['*'], now()->addHours(2))->plainTextToken;
        return response()->json(['token' => $token]);
    }

      private function purgeExpiredTokens()
    {
        // Only deletes if token expired 2 hours ago
        $dateTimetoPurge = now()->subHours(2);
        DB::table('personal_access_tokens')->where('expires_at', '<', $dateTimetoPurge)->delete();
    }

     private function revokeCurrentToken(User $user)
    {
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', $currentTokenId)->delete();
    }

}
