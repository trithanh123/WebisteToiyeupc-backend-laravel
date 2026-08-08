<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
   protected function prepareForValidation()
    {
        $this->merge([
            'don_toithieu' => $this->filled('don_toithieu') ? $this->don_toithieu : 0,
            'giam_toida'   => $this->filled('giam_toida') ? $this->giam_toida : null,
            'dasudung'     => 0, 
        ]);
    }
    public function rules(): array
    {
        return [
            'tenkhuyenmai'           => 'required|string|max:255',
            'ma_voucher'             => 'required|string|max:100|unique:khuyen_mai,ma_voucher',
            'loai_giamgia'           => 'required|string|in:Phần trăm,Số tiền',
            'gia_trigiam'            => 'required|numeric|min:0',
            'don_toithieu'           => 'nullable|numeric|min:0',
            'giam_toida'             => 'nullable|numeric|min:0',
            'soluongma'              => 'required|integer|min:0',
            'dasudung'               => 'required|integer',     
            'ngaybdchuongtrinh'      => 'required|date',
            'ngayketthucchuongtrinh' => 'required|date|after:ngaybdchuongtrinh|after:today',
        ];
    }
    public function messages(){
        return [
            'ma_voucher.unique'=> 'Mã voucher này đã tồn tại, vui lòng nhập mã khác.',
            'ngayketthucchuongtrinh.after'=> 'Ngày kết thúc phải lớn hơn ngày bắt đầu.',
            'ngayketthucchuongtrinh.after_or_equal'  => 'Ngày kết thúc không được là ngày trong quá khứ.',
            'gia_trigiam.min'=> 'Giá trị giảm tuyệt đối không được là số âm.',
            'soluongma.min'=> 'Số lượng mã không được là số âm.',
        ];
    }
    public function withValidator($validator){
        $validator->after(function($validator){
            if($this->loai_giamgia === 'Phần trăm' && $this->gia_trigiam >100){
                 $validator->errors()->add("gia_trigiam","Giá trị giảm không được vượt quá 100%");
            }
        });
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
