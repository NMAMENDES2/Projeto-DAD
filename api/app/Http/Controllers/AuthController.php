<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Limpa tokens expirados (mais de 2 horas)
     */
    private function purgeExpiredTokens()
    {
        $dateTimetoPurge = now()->subHours(2);
        DB::table('personal_access_tokens')
            ->where('expires_at', '<', $dateTimetoPurge)
            ->delete();
    }

    /**
     * Revoga o token atual do utilizador
     */
    private function revokeCurrentToken(User $user)
    {
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', $currentTokenId)->delete();
    }

    /**
     * REGISTO DE NOVO UTILIZADOR (G1)
     * - Cria novo player com 10 brain coins iniciais
     * - Foto opcional
     * 
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            $validated = $request->validated();

            // Processar foto se enviada
            $photoFilename = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoFilename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                
                // Guardar em storage/app/public/photos
                $photo->move(storage_path('app/public/photos'), $photoFilename);
            }

            // Criar utilizador com 10 brain coins (bónus inicial - G1 requirement)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nickname' => $validated['nickname'],
                'password' => Hash::make($validated['password']),
                'type' => 'P', // Player
                'brain_coins_balance' => 10, // ✅ BÓNUS INICIAL (G1)
                'photo_filename' => $photoFilename,
                'blocked' => false,
            ]);

            // Criar token de autenticação (válido por 2 horas)
            $token = $user->createToken('authToken', ['*'], now()->addHours(2))->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! You received 10 brain coins as welcome bonus.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nickname' => $user->nickname,
                    'type' => $user->type,
                    'brain_coins_balance' => $user->brain_coins_balance,
                    'photo_filename' => $user->photo_filename,
                    'blocked' => $user->blocked,
                ],
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LOGIN (G1)
     * - Valida credenciais
     * - Verifica se conta está bloqueada
     * - Retorna token de autenticação
     * 
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $this->purgeExpiredTokens();
        
        $credentials = $request->validated();

        // Tentar autenticar
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials. Please check your email and password.'
            ], 401);
        }

        $user = Auth::user();

        // ✅ Verificar se o utilizador está bloqueado (G1)
        if ($user->blocked) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked. Please contact support.'
            ], 403);
        }

        // Criar token (válido por 2 horas)
        $token = $user->createToken('authToken', ['*'], now()->addHours(2))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nickname' => $user->nickname,
                'type' => $user->type,
                'brain_coins_balance' => $user->brain_coins_balance,
                'photo_filename' => $user->photo_filename,
                'blocked' => $user->blocked,
            ],
            'token' => $token
        ], 200);
    }

    /**
     * LOGOUT (G1)
     * Revoga o token atual
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->purgeExpiredTokens();
        $this->revokeCurrentToken($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.'
        ], 200);
    }

    /**
     * REFRESH TOKEN
     * Revoga token atual e cria um novo
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $this->purgeExpiredTokens();
        $this->revokeCurrentToken($request->user());
        
        $token = $request->user()->createToken('authToken', ['*'], now()->addHours(2))->plainTextToken;
        
        return response()->json([
            'success' => true,
            'token' => $token
        ], 200);
    }
}
