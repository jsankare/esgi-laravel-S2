<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A room name is required',
            'name.max' => 'Room name cannot be longer than 255 characters',
            'password.min' => 'Password must be at least 6 characters',
        ];
    }
}
