<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffTransferRequest extends FormRequest
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
            'ma_kho_xuat' => 'required',
            'ma_kho_nhap' => 'required',
            'ly_do' => 'required',
            'ghi_chu' => 'required',
            'chi_tiet' => 'required',
            'chi_tiet.*' => 'required',
            'chi_tiet.*.ma_sanpham' => 'required',
            'chi_tiet.*.so_luong' => 'required',
        ];
    }
    public function message(){
        return [
            'ma_kho_xuat.required' => 'Mã kho xuất không được để trống',
            'ma_kho_nhap.required' => 'Mã kho nhập không được để trống',
            'ly_do.required' => 'Lý do không được để trống',
            'ghi_chu.required' => 'Ghi chú không được để trống',
            'chi_tiet.required' => 'Chi tiết không được để trống',
            'chi_tiet.*.ma_sanpham.required' => 'Mã sản phẩm không được để trống',
            'chi_tiet.*.so_luong.required' => 'Số lượng không được để trống',
        ];
    }
}
