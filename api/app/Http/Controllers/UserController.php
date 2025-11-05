<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * VER PERFIL PRÓPRIO (G1)
     * Retorna dados do utilizador autenticado
     */
    public function showMe(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * EDITAR PERFIL (G1)
     * Permite alterar name, nickname, email
     */
    public function edit(UpdateUserRequest $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['nickname'])) {
                $user->nickname = $validated['nickname'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'user' => new UserResource($user)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ALTERAR FOTO DE PERFIL (G1)
     */
    public function updatePicture(UpdateImageRequest $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Verificar se é o próprio utilizador ou admin
            if ($request->user()->id !== $user->id && $request->user()->type !== 'A') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }

            // Apagar foto antiga se existir
            if ($user->photo_filename) {
                $oldPhotoPath = storage_path('app/public/photos/' . $user->photo_filename);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Guardar nova foto
            $photo = $request->file('profile_picture');
            $photoFilename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(storage_path('app/public/photos'), $photoFilename);

            $user->photo_filename = $photoFilename;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully.',
                'photo_filename' => $photoFilename
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ALTERAR PASSWORD (G1)
     */
    public function changePassword(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            if ($request->user()->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }

            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:3|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ELIMINAR PRÓPRIA CONTA (G1)
     * ✅ Requer confirmação explícita (password)
     * ✅ Soft delete - preserva histórico
     */
    public function destroy(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'password' => 'required|string',
            ]);

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect. Account not deleted.'
                ], 403);
            }

            $user->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Account successfully deleted. All brain coins have been forfeited.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * SERVIR IMAGEM DE PERFIL (Rota pública)
     */
    public function image($filename)
    {
        $path = storage_path('app/public/photos/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file, 200)->header('Content-Type', $type);
    }

    // ========================================
    // FUNCIONALIDADES DE ADMIN (G5)
    // ✅ Verificação direta no controller
    // ========================================

    /**
     * CRIAR ADMIN (apenas admins)
     */
    public function createAdmin(Request $request)
    {
        // ✅ Verificar se é admin
        if ($request->user()->type !== 'A') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'nickname' => 'required|unique:users,nickname|min:1',
                'name' => 'required|min:1',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:3',
            ]);

            $user = User::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'nickname' => $validated['nickname'],
                'password' => Hash::make($validated['password']),
                'type' => 'A',
                'brain_coins_balance' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully.',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LISTAR UTILIZADORES (apenas admins)
     */
    public function index(Request $request)
    {
        // ✅ Verificar se é admin
        if ($request->user()->type !== 'A') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $type = $request->input('type');

        if (!$type) {
            $users = User::paginate(15);
        } else {
            $users = User::where('type', $type)->paginate(15);
        }

        return response()->json($users);
    }

    /**
     * ELIMINAR UTILIZADOR (apenas admins)
     */
    public function userDestroy(Request $request, $id)
    {
        // ✅ Verificar se é admin
        if ($request->user()->type !== 'A') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * BLOQUEAR/DESBLOQUEAR UTILIZADOR (apenas admins)
     */
    public function block(Request $request, $userId)
    {
        // ✅ Verificar se é admin
        if ($request->user()->type !== 'A') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        try {
            $user = User::findOrFail($userId);

            $request->validate([
                'blocked' => 'required|boolean',
            ]);

            $user->blocked = $request->blocked;
            $user->save();

            $status = $user->blocked ? 'blocked' : 'unblocked';

            return response()->json([
                'success' => true,
                'message' => "User {$status} successfully.",
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
