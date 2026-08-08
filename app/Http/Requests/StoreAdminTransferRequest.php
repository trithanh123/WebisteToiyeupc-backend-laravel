<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminTransferRequest extends FormRequest
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
            "ma_kho_xuat"=>"required",
            "ma_kho_nhap"=>"required",
            "ly_do"=>"required",
            "chi_tiet"=>"required|array",
            "chi_tiet.*.ma_sanpham"=>"required",
            "chi_tiet.*.so_luong"=>"required|integer|min:1",
            
        ];
    }
    public function messages(): array
    {
        return [
            'ma_kho_xuat.required' => 'Vui lòng chọn kho xuất.',
            'ma_kho_nhap.required' => 'Vui lòng chọn kho nhập.',
            'ly_do.required' => 'Vui lòng nhập lý do điều chuyển.',
            'chi_tiet.required' => 'Vui lòng chọn ít nhất 1 sản phẩm.',
            'chi_tiet.array' => 'Chi tiết không hợp lệ.',
            'chi_tiet.*.ma_sanpham.required' => 'Mã sản phẩm không được để trống.',
            'chi_tiet.*.so_luong.required' => 'Số lượng không được để trống.',
            'chi_tiet.*.so_luong.integer' => 'Số lượng phải là số nguyên.',
            'chi_tiet.*.so_luong.min' => 'Số lượng phải lớn hơn 0.',
        ];
    }
}
