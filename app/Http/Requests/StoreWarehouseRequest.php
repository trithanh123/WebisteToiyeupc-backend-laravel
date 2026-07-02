<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'nha_cung_cap'=> 'required|string|max:255',
            'ngay_nhap'=> 'required|date',
            'ma_chi_nhanh'=> 'required|integer|exists:chi_nhanh,id_chinhanh', 
            'san_phams' => 'required|array|min:1', 
            'san_phams.*.ma_san_pham'=> 'required|integer|exists:san_pham,id_sanpham', 
            'san_phams.*.serials'=> 'nullable|array',
            'san_phams.*.serials.*'=> 'required_with:san_phams.*.serials|string|distinct', 
            'san_phams.*.soluongtonkho'=> 'required|integer|min:1', 
            'san_phams.*.soluongkhothap'=> 'required|integer|min:0',
        ];
    }
    public function messages(){
        return [
            'nha_cung_cap.required'=> 'Vui lòng nhập tên nhà cung cấp.',
            'ngay_nhap.required' => 'Vui lòng chọn ngày nhập kho.',
            'ngay_nhap.date'=> 'Ngày nhập kho không đúng định dạng.',
            'ma_chi_nhanh.exists'=> 'Chi nhánh đã chọn không tồn tại.',
            'san_phams.required'=> 'Phiếu nhập kho phải có ít nhất 1 sản phẩm.',
            'san_phams.min'=> 'Phiếu nhập kho phải có ít nhất 1 sản phẩm.',
            'san_phams.*.ma_san_pham.exists'=> 'Sản phẩm ở dòng thứ :index không tồn tại trong hệ thống.',
            'san_phams.*.serials.*.distinct'=> 'Phát hiện mã Serial bị quét trùng lặp trong cùng một mặt hàng!',
            'san_phams.*.soluongtonkho.min'=> 'Số lượng nhập kho phải từ 1 trở lên.',
            'san_phams.*.soluongkhothap.min'=> 'Định mức kho thấp không được là số âm.',
        ];
    }
    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sanPhams = $this->input('san_phams', []);
            if (!is_array($sanPhams)) return;
            foreach ($sanPhams as $index => $item) {
                if (isset($item['serials']) && is_array($item['serials']) && count($item['serials']) > 0) {
                    $soLuongKhaiBao = (int) ($item['soluongtonkho'] ?? 0);
                    $soSerialThucTe  = count($item['serials']);
                    if ($soSerialThucTe !== $soLuongKhaiBao) {
                        $dieuChinhIndex = $index + 1;
                        $validator->errors()->add(
                            "san_phams.{$index}.serials", 
                            "Sản phẩm dòng thứ {$dieuChinhIndex} khai báo nhập {$soLuongKhaiBao} cái, nhưng chỉ quét được {$soSerialThucTe} mã Serial!"
                        );
                    }
                }
            }
        });
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Dữ liệu phiếu nhập kho không hợp lệ.',
            'errors'  => $validator->errors()
        ], 422));
    }
}
