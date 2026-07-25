<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    { 
        $id = $this->route('id');
        return [
            'ten'=> 'nullable|string|max:255',
            'sdt'=> ['nullable','string','regex:/^[0-9]{9,11}$/','unique:nguoi_dung,sdt,' . $id . ',id_nguoidung',],
            'phanquyen' => 'nullable|integer|in:1,2,3',
        ];
    }
    public function messages(){
        return [
            'sdt.regex'    => 'Số điện thoại không hợp lệ',
            'sdt.unique'   => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.',
            'phanquyen.in' => 'Phân quyền không hợp lệ.',
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
