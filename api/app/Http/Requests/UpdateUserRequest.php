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
        return [
            'name' => 'sometimes|string|min:2|max:255',
            'nickname' => 'sometimes|string|min:2|max:20|unique:users,nickname,' . $this->user->id,
            'email' => 'sometimes|email|unique:users,email,' . $this->user->id,
            'custom' => 'sometimes|json',
        ];
    }
}
