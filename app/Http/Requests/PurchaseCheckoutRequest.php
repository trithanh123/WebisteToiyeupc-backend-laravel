<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
class PurchaseCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'ma_chinhanh' => 'required|integer',
            'ma_khuyenmai' => 'nullable|integer',
            'ma_diachinguoidung' => 'required|integer',
            'phuong_thuc_tt' => 'required|string|in:Tiền mặt,VNPay',
            'ghichu' => 'nullable|string',
            'cart_items' => 'required|array', 
            'tongtien' => 'required|numeric'
        ];
    }
    public function messages(){
        return [
            'ma_chinhanh.required' => 'Vui lòng chọn chi nhánh.',
            'ma_diachinguoidung.required' => 'Vui lòng chọn địa chỉ người dùng.',
            'phuong_thuc_tt.required' => 'Vui lòng chọn phương thức thanh toán.',
            'cart_items.required' => 'Vui lòng chọn sản phẩm.',
            'tongtien.required' => 'Vui lòng nhập tổng tiền.',
        ];
    }
}
