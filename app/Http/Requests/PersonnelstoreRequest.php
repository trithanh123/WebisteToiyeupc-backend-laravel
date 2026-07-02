<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Nhan_vien;
class PersonnelstoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'chucvu'           => 'required|string|max:50',
            'machinhanh'       => 'required|exists:chi_nhanh,id_chinhanh',
            'existing_user_id' => 'nullable|exists:nguoi_dung,id_nguoidung',
            'ten'              => 'required_without:existing_user_id|string|max:255',
            'email'            => 'required_without:existing_user_id|email|max:255|unique:nguoi_dung,email',
            'matkhau'          => 'required_without:existing_user_id|string|min:6',
            'sdt'              => ['required_without:existing_user_id', 'regex:/^0[0-9]{9}$/'],
        ];
    }
    public function messages(){
      return [
        'ten.required_without'     => 'Vui lòng nhập tên nhân viên.',
            'email.required_without'   => 'Vui lòng nhập email.',
            'email.unique'             => 'Email này đã được sử dụng cho một tài khoản khác.',
            'matkhau.required_without' => 'Vui lòng nhập mật khẩu.',
            'sdt.required_without'     => 'Vui lòng nhập số điện thoại.',
            'sdt.regex'                => 'Số điện thoại không hợp lệ (phải bắt đầu bằng số 0 và đúng 10 số).',
            'machinhanh.exists'       => 'Mã chi nhánh không tồn tại trong hệ thống.',
            'existing_user_id.exists'  => 'Tài khoản người dùng không tồn tại.',
      ];
    }
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('existing_user_id')) {
                $checkExists = nhan_vien::where('id_nguoidung', $this->existing_user_id)->first();     
                if ($checkExists && $checkExists->nguoi_dung->phanquyen != -1) {
                    $validator->errors()->add('existing_user_id', 'Người dùng này hiện đã là nhân viên rồi!');
                }
            }
        });
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
