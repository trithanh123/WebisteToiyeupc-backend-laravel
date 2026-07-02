<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\nhan_vien;
class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $nhanVienId = $this->route('id');
        $nhanVien = nhan_vien::find($nhanVienId);
        $userId = $nhanVien ? $nhanVien->id_nguoidung : null;
        return [
            'ten'           => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255|unique:nguoi_dung,email,' . $userId . ',id_nguoidung',
            'matkhau'       => 'nullable|string|min:6',
            'sdt'           => ['nullable', 'regex:/^0[0-9]{9}$/'], 
            'chucvu'        => 'nullable|string|max:50',
            'machinhanh'    => 'nullable|exists:chi_nhanh,id_chinhanh',
        ];
    }
    public function messages(){
        return [
           'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã được sử dụng cho một tài khoản khác.',
            'matkhau.min'        => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'sdt.regex'          => 'Số điện thoại không hợp lệ (phải bắt đầu bằng số 0 và có 10 chữ số).',
            'machinhanh.exists'  => 'Mã chi nhánh không tồn tại trong hệ thống.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Dữ liệu không hợp lệ',
            'errors'  => $validator->errors()
        ], 422));
    }
}
