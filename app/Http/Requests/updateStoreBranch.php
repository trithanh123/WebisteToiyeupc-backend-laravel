<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class updateStoreBranch extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'ten_chinhanh'    => 'nullable|string|max:255',
            'Ma_chi_nhanh'    => 'nullable|string|max:100|unique:chi_nhanh,Ma_chi_nhanh,' . $this->id . ',id_chinhanh',
            'SDT_Chi_nhanh'   => 'nullable|string|max:20',            
            'email_chi_nhanh' => 'nullable|email|max:255',
            'diachi_chitiet'  => 'nullable|string|max:255',
            'maso_phuong'     => 'nullable|integer',
            'Maso_TP'         => 'nullable|integer',
            'Maso_TInh'       => 'nullable|integer',
            'map_link'        => 'nullable|string',
        ];
    }
     public function messages()
     {
        return [
            'Ma_chi_nhanh.unique'    => 'Mã chi nhánh này đã tồn tại, vui lòng chọn mã khác.',
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
