<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation()
    {
        if ($this->has('slug') && $this->slug !== null) {
            $this->merge([
                'slug' => Str::slug($this->slug),
            ]);
        }
    }
    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'ten_danhmuc'  => 'nullable|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:danh_muc,slug,' . $id . ',id_danhmuc',
            'danhmuc_cha'  => 'nullable|integer|exists:danh_muc,id_danhmuc',
            'hinhanh_icon' => 'nullable|string|max:255',
            'is_active'    => 'nullable|boolean',
        ];
    }
    public function messages(){
        return [
            'slug.unique'        => 'Slug này đã tồn tại, vui lòng chọn slug khác.',
            'danhmuc_cha.exists' => 'Danh mục cha không tồn tại trong hệ thống.',
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
