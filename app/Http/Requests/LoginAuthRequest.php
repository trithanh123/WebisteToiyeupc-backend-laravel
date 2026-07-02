<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class LoginAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'email'    => 'required|string',
            'password' => 'required|string',
        ];
    }
    public function messages(): array
    {
        return [
            'email.required'    => 'Vui lòng nhập Email hoặc Số điện thoại.',
            'email.string'      => 'Email/SĐT phải là chuỗi ký tự.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.string'   => 'Mật khẩu phải là chuỗi ký tự.',
        ];
    }
}
