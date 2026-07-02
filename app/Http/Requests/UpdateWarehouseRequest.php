<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
             'soluongtonkho' => 'nullable|integer|min:0',
            'soluongkhothap' => 'nullable|integer|min:0',
        ];
    }
    public function messages(){
        return [
            'soluongtonkho.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'soluongtonkho.min' => 'Số lượng tồn kho không được là số âm.',
            'soluongkhothap.integer' => 'Số lượng tồn kho thấp phải là số nguyên.',
            'soluongkhothap.min' => 'Số lượng tồn kho thấp không được là số âm.',
        ];
    }
}
