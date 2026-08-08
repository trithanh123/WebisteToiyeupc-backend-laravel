<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'identifier' => 'required|string',
            'otp'        => 'required|string|size:6',
        ];
    }
    public function messages(){
        return [
            'identifier.required' => 'Vui lòng cung cấp Email hoặc Số điện thoại.',
            'otp.required'        => 'Vui lòng nhập mã OTP.',
            'otp.size'            => 'Mã OTP phải có đúng 6 ký tự.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Dữ liệu không hợp lệ.',
            'errors'  => $validator->errors()
        ], 422));
    }
}
