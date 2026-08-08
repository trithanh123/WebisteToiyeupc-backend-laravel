<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StorePRoductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'ma_danhmuc'     => 'required|integer|exists:danh_muc,id_danhmuc',
            'masp'           => 'required|string|max:255|unique:san_pham,masp',
            'tensp'          => 'required|string|max:255',
            'gia'            => 'required|integer|min:1',
            'thumbail'       => 'nullable|string', 
            'motasanpham'    => 'nullable|string',
            'specifications' => 'nullable|array',
            'weight'         => 'nullable|numeric|min:0'
        ];
    }
    public function messages(){
        return [
            'ma_danhmuc.required' => 'Vui lòng chọn danh mục.',
            'ma_danhmuc.exists'   => 'Danh mục không tồn tại.',
            'masp.required'       => 'Mã sản phẩm không được để trống.',
            'masp.unique'         => 'Mã sản phẩm này đã tồn tại.',
            'tensp.required'      => 'Tên sản phẩm không được để trống.',
            'gia.required'        => 'Giá sản phẩm không được để trống.',
            'gia.min'             => 'Giá sản phẩm phải lớn hơn 0.',
            'specifications.array'=> 'Thông số kỹ thuật phải là một mảng.',
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
