<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required_if:has_password,true', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required_if' => 'Password is required to join this room',
        ];
    }
}
