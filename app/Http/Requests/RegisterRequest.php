<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'email' => 'required_without:sdt|nullable|email|unique:nguoi_dung,email',
            'sdt' => 'required_without:email|nullable|unique:nguoi_dung,sdt',
            'ho' => 'required|string',
            'ten' => 'required|string',
            'matkhau' => 'required|string|confirmed|min:8', 
            'matkhau_confirmation' => 'required|string|min:8',
        ];
    }
    public function messages(){
        return [
            'email.required_without' => 'Vui lòng nhập Email',
            'email.unique' => 'Email này đã được sử dụng',
            'email.email' => 'Email không đúng định dạng',
            'sdt.required_without' => 'Vui lòng nhập Số điện thoại',
            'sdt.unique' => 'Số điện thoại này đã được sử dụng',
            'ho.required' => 'Họ không được để trống',
            'ten.required' => 'Tên không được để trống',
            'matkhau.required' => 'Mật khẩu không được để trống',
            'matkhau.confirmed' => 'Mật khẩu không khớp',
            'matkhau.min' => 'Mật khẩu phải có trên 8 ký tự',
           
         
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
