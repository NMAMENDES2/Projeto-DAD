<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1',
            'nickname' => 'required|string|unique:users|min:1',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:3',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must have at least 1 character.',
            'nickname.required' => 'Nickname is required.',
            'nickname.unique' => 'This nickname is already taken.',
            'nickname.min' => 'Nickname must have at least 1 character.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 3 characters.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'Image must be jpeg, png, jpg or gif.',
            'photo.max' => 'Image size cannot exceed 2MB.',
        ];
    }
}
