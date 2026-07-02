<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
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
            'email' => 'required_without:sdt',
            'sdt' => 'required_without:email',
            'ho' => 'required|string',
            'ten' => 'required|string',
            'matkhau' => 'required|string|confirmed|min:8', 
        ];
    }
    public function messages(){
        return [
            'email.required_without' => 'Vui lòng nhập Email',
            'sdt.required_without' => 'Vui lòng nhập Số điện thoại',
            'ho.required' => 'Họ không được để trống',
            'ten.required' => 'Tên không được để trống',
            'matkhau.required' => 'Mật khẩu không được để trống',
            'matkhau.confirmed' => 'Mật khẩu không khớp',
            'matkhau.min' => 'Mật khẩu phải có ít nhất 8 ký tự'
        ];
    }
}
