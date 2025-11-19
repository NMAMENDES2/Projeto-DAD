<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * LISTAR UTILIZADORES (Apenas Admin)
     */
    public function index(Request $request)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $type = $request->query('type');

        $users = $type
            ? User::where('type', $type)->paginate(15)
            : User::paginate(15);

        return UserResource::collection($users);
    }

    /**
     * CRIAR UTILIZADOR (Admin)
     */
    public function store(Request $request)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'nickname' => 'required|string|unique:users,nickname',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:3',
            'type' => 'required|in:A,P',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'nickname' => $validated['nickname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'type' => $validated['type'],
            'coins_balance' => $validated['type'] === 'P' ? 0 : 0,
            'blocked' => false,
        ]);

        return new UserResource($user);
    }

    /**
     * MOSTRAR UM UTILIZADOR
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * ATUALIZAR PERFIL DO UTILIZADOR
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // Apenas o próprio ou um admin
        if ($request->user()->id !== $user->id && $request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update($request->validated());

        return new UserResource($user);
    }

    /**
     * APAGAR UTILIZADOR (Soft Delete)
     */
    public function destroy(Request $request, User $user)
    {
        // O próprio tem de confirmar com password
        if ($request->user()->id === $user->id) {
            $request->validate(['password' => 'required|string']);

            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Password incorrect'], 403);
            }
        }
        // Admin pode apagar diretamente
        else if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * ALTERAR PASSWORD
     */
    public function updatePassword(UpdatePasswordRequest $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * UPLOAD DE FOTO DE PERFIL
     */
    public function uploadPhoto(UpdateImageRequest $request, $id)
    {
        $user = User::findOrFail($id);

        // Apenas o próprio ou admin
        if ($request->user()->id !== $user->id && $request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->photo_avatar_filename) {
            $oldPath = storage_path('app/public/avatars/' . $user->photo_avatar_filename);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $photo = $request->file('photo');
        $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
        $photo->move(storage_path('app/public/avatars'), $filename);

        $user->photo_avatar_filename = $filename;
        $user->save();

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_avatar_filename' => $filename
        ]);
    }

    /**
     * BLOQUEAR / DESBLOQUEAR UTILIZADOR (Admin)
     */
    public function toggleBlock(Request $request, $id)
    {
        if ($request->user()->type !== 'A') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['blocked' => 'required|boolean']);

        $user = User::findOrFail($id);
        $user->blocked = $request->blocked;
        $user->save();

        return response()->json([
            'message' => 'User block status updated',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * SERVIR FOTO DO USER
     */
    public function showPhoto($filename)
    {
        $path = storage_path('app/public/avatars/' . $filename);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        return response()->file($path);
    }
    /**
     * DEVOLVE OS DADOS DO UTILIZADOR AUTENTICADO
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
