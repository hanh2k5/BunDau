<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Email là bắt buộc',
            'email.email'       => 'Email không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min'      => 'Mật khẩu phải có ít nhất 3 ký tự',
        ];
    }
}
