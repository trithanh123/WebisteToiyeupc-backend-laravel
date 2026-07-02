<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class updateVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation()
    {
        if ($this->has('don_toithieu')) {
            $this->merge([
                'don_toithieu' => ($this->don_toithieu !== null && $this->don_toithieu !== '') ? $this->don_toithieu : 0,
            ]);
        }
        if ($this->has('giam_toida')) {
            $this->merge([
                'giam_toida' => ($this->giam_toida !== null && $this->giam_toida !== '') ? $this->giam_toida : null,
            ]);
        }
    }
    public function rules(): array
    {   
        $id = $this->route('id');
        return [
            'Tenkhuyenmai'=> 'nullable|string|max:255',
            'ma_khuyenmai'=> 'nullable|string|max:100|unique:khuyen_mai,ma_khuyenmai,' . $id . ',id_khuyenmai',
            'loai_giamgia' => 'nullable|string|in:Phần trăm,Số tiền',
            'gia_trigiam'=> 'nullable|numeric|min:0',
            'don_toithieu'=> 'nullable|numeric|min:0',
            'giam_toida'=> 'nullable|numeric|min:0',
            'soluongma'=> 'nullable|integer|min:0',
            'ngaybdchuongtrinh'=> 'nullable|date',
            'ngayketthucchuongtrinh'=> 'nullable|date|after:ngaybdchuongtrinh',
        ];
    }
    public function messages(){
        return [
            'ma_khuyenmai.unique'=> 'Mã voucher này đã tồn tại, vui lòng nhập mã khác.',
            'ngayketthucchuongtrinh.after' => 'Ngày kết thúc bắt buộc phải lớn hơn ngày bắt đầu.',
            'gia_trigiam.min'=> 'Giá trị giảm tuyệt đối không được là số âm.',
            'soluongma.min'=> 'Số lượng mã tuyệt đối không được là số âm.',
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
