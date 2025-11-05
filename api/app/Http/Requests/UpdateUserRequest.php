<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;
        
        return [
            'name' => 'sometimes|string|min:1',
            'nickname' => 'sometimes|string|unique:users,nickname,' . $userId,
            'email' => 'sometimes|string|email|unique:users,email,' . $userId,
        ];
    }
}
