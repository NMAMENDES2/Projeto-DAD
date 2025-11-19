<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'nickname' => 'required|string|min:2|max:20|unique:users,nickname',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:3|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ];
    }
}
