<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\diachi_nguoidung;
class StoreAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ten_nguoinhan'   => 'required|string|max:255',
            'sdt_nguoinhan'   => 'required|string|regex:/^(0)[0-9]{9}$/',
            'ma_thanhpho'     => 'required|integer',
            'ma_quan'         => 'required|integer',
            'ma_phuong'       => 'required|integer',
            'diachi_chitiet'  => 'required|string|max:255',
            'matudien_diachi' => 'nullable|boolean'
        ];
    }

    
    public function messages(): array
    {
        return [
            'ten_nguoinhan.required'  => 'Họ tên người nhận không được để trống.',
            'sdt_nguoinhan.required'  => 'Số điện thoại không được để trống.',
            'sdt_nguoinhan.regex'     => 'Số điện thoại không đúng định dạng (VD: 0912345678).',
            'ma_thanhpho.required'    => 'Vui lòng chọn Tỉnh/Thành phố.',
            'ma_quan.required'        => 'Vui lòng chọn Quận/Huyện.',
            'ma_phuong.required'      => 'Vui lòng chọn Phường/Xã.',
            'diachi_chitiet.required' => 'Địa chỉ cụ thể không được để trống.',
        ];
    }

   
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            if ($user) {
                $count = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)->count();

                if ($count >= 5) {
                    $validator->errors()->add('limit_address', 'Không thể thêm địa chỉ mới. Bạn đã đạt giới hạn tối đa (5 địa chỉ).');
                }
            }
        });
    }
}
