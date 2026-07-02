<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use App\Models\danh_muc;
class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation()
    {
        if ($this->ten_danhmuc) {
            $slug = $this->slug ? Str::slug($this->slug) : Str::slug($this->ten_danhmuc);
            $originalSlug = $slug;
            $count = 1;
            while (danh_muc::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $this->merge([
                'slug' => $slug,
            ]);
        }
    }   
    public function rules(): array
    {
        return [
            'ten_danhmuc'  => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:danh_muc,slug',
            'danhmuc_cha'  => 'nullable|integer|exists:danh_muc,id_danhmuc',
            'hinhanh_icon' => 'nullable|string|max:255',
            'is_active'    => 'nullable|boolean',
        ];
    }
    public function messages(){
        return [
            'ten_danhmuc.required' => 'Vui lòng nhập tên danh mục.',
            'ten_danhmuc.max'      => 'Tên danh mục không được vượt quá 255 ký tự.',
            'slug.unique'          => 'Slug này đã tồn tại, vui lòng chọn slug khác.',
            'danhmuc_cha.exists'   => 'Danh mục cha không tồn tại trong hệ thống.',
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
