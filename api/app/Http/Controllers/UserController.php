<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * LISTAR UTILIZADORES (Admin)
     */
    public function index(Request $request)
    {
        // Verificar se é admin
        if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $type = $request->input('type');
        
        $users = $type 
            ? User::where('type', $type)->paginate(15)
            : User::paginate(15);

        return response()->json($users);
    }

    /**
     * CRIAR UTILIZADOR/ADMIN
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:1',
            'nickname' => 'required|unique:users|min:1',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:3',
            'type' => 'required|in:P,A',
        ]);

        // Se não for admin, só pode criar Players
        if ($request->user()->type !== 'A' && $validated['type'] === 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nickname' => $validated['nickname'],
            'password' => Hash::make($validated['password']),
            'type' => $validated['type'],
            'brain_coins_balance' => $validated['type'] === 'P' ? 10 : 0,
        ]);

        return new UserResource($user);
    }

    /**
     * VER UTILIZADOR ESPECÍFICO
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * ATUALIZAR UTILIZADOR (G1)
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // Verificar permissões
        if ($request->user()->id !== $user->id && $request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update($request->validated());
        
        return new UserResource($user);
    }

    /**
     * ELIMINAR UTILIZADOR (G1)
     */
    public function destroy(Request $request, User $user)
    {
        // Se for o próprio user, requer confirmação
        if ($request->user()->id === $user->id) {
            $request->validate([
                'password' => 'required|string',
            ]);

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Password is incorrect.'
                ], 403);
            }
        }
        // Se for admin, pode eliminar sem confirmação
        else if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete(); // Soft delete

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * UPLOAD DE FOTO (G1)
     */
    public function uploadPhoto(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Verificar permissões
        if ($request->user()->id !== $user->id && $request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Apagar foto antiga
        if ($user->photo_filename) {
            $oldPath = storage_path('app/public/photos/' . $user->photo_filename);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Guardar nova foto
        $photo = $request->file('photo');
        $photoFilename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
        $photo->move(storage_path('app/public/photos'), $photoFilename);

        $user->photo_filename = $photoFilename;
        $user->save();

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_filename' => $photoFilename
        ]);
    }

    /**
     * ALTERAR PASSWORD (G1)
     */
    public function updatePassword(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:3|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * BLOQUEAR/DESBLOQUEAR (Admin)
     */
    public function toggleBlock(Request $request, $userId)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($userId);

        $request->validate([
            'blocked' => 'required|boolean',
        ]);

        $user->blocked = $request->blocked;
        $user->save();

        return response()->json([
            'message' => 'User status updated',
            'user' => $user
        ]);
    }

    /**
     * SERVIR FOTO
     */
    public function showPhoto($filename)
    {
        $path = storage_path('app/public/photos/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
