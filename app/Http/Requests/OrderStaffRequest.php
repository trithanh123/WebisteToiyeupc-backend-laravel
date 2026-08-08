<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderStaffRequest extends FormRequest
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
        $rules = ['trang_thai_dh' => 'required|string'];
        
        if ($this->input('trang_thai_dh') === 'Đang giao hàng') {
            $rules['serials'] = 'required|array';
            $rules['serials.*.id_chitietdh'] = 'required|integer';
            $rules['serials.*.id_serial'] = 'required|integer';
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'trang_thai_dh.required' => 'Trạng thái đơn hàng là bắt buộc.',
            'trang_thai_dh.string' => 'Trạng thái đơn hàng phải là một chuỗi.',
            'serials.required' => 'Vui lòng cung cấp danh sách serial khi giao hàng.',
            'serials.array' => 'Danh sách serial không hợp lệ.',
            'serials.*.id_chitietdh.required' => 'Mã chi tiết đơn hàng là bắt buộc cho từng serial.',
            'serials.*.id_serial.required' => 'Mã serial là bắt buộc cho từng sản phẩm.',
        ];
    }
}
