<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'ma_danhmuc'     => 'nullable|integer|exists:danh_muc,id_danhmuc',
            'masp'           => 'nullable|string|max:255|unique:san_pham,masp,' . $id . ',id_sanpham',
            'tensp'          => 'nullable|string|max:255',
            'gia'            => 'nullable|integer|min:0',
            'thumbail'       => 'nullable|string',
            'motasanpham'    => 'nullable|string',
            'specifications' => 'nullable|array',
        ];
    }
    public function messages(){
        return [
            'ma_danhmuc.exists' => 'Danh mục đã chọn không tồn tại trong hệ thống.',
            'masp.unique'       => 'Mã sản phẩm này đã tồn tại, vui lòng chọn mã khác.',
            'gia.min'           => 'Giá sản phẩm không được nhỏ hơn 0.',
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
