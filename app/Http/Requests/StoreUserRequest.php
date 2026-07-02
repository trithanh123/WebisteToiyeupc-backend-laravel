<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {  
        return [
            'ten' => 'required|string|max:255',
            'email' => [
                'required_without:sdt',
                'nullable',
                'string',
                'email',
                'max:255',
                'unique:nguoi_dung,email',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return; 
                    $val = strtolower($value);
                    if (!str_ends_with($val, '@gmail.com') && !str_ends_with($val, '@toiyeupc.vn')) {
                        $fail('Chỉ hỗ trợ đăng ký bằng tài khoản @gmail.com hoặc @toiyeupc.vn!');
                    }
                },
            ],
            'sdt' => [
                'required_without:email',
                'nullable',
                'string',
                'regex:/^[0-9]{9,11}$/',
                'unique:nguoi_dung,sdt',
            ],
            'matkhau'   => 'required|string|min:8',
            'phanquyen' => 'nullable|integer|in:1,2,3',
        ];
    }
    public function messages(){
        return [
            'email.required_without' => 'Vui lòng nhập Email hoặc Số điện thoại để tạo tài khoản.',
            'sdt.required_without'   => 'Vui lòng nhập Email hoặc Số điện thoại để tạo tài khoản.',
            'ten.required'      => 'Vui lòng nhập họ tên.',
            'email.email'       => 'Email không hợp lệ. Vui lòng nhập đúng định dạng (ví dụ: abc@gmail.com).',
            'email.unique'      => 'Email này đã được sử dụng bởi tài khoản khác.',
            'sdt.regex'         => 'Số điện thoại chỉ được chứa chữ số (9-11 ký tự). Không được nhập chữ hoặc ký tự đặc biệt.',
            'sdt.unique'        => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.',
            'matkhau.required'  => 'Vui lòng nhập mật khẩu.',
            'matkhau.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'phanquyen.in'      => 'Phân quyền không hợp lệ.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Dữ liệu không hợp lệ.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
