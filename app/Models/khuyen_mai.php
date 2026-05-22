<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class khuyen_mai extends Model
{
    use HasFactory;
    // 1. CẤU HÌNH BẢNG
    protected $table = 'khuyen_mai';
    protected $primaryKey = 'id_khuyenmai';

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU
    protected $fillable = [
        'TenKhuyenMai',
        'Ma_voucher',
        'Loai_giamgia',      // Kiểu chuỗi ENUM ('Phần trăm', 'Số tiền')
        'gia_trigiam',  // Bổ sung: Số tiền hoặc số % sẽ được giảm
        'don_toithieu',
        'giam_toida',
        'Soluongma',
        'dasudung',
        'Ngaybdchuongtrinh',
        'Ngayketthucchuongtrinh',
    ];

    // 3. ÉP KIỂU DỮ LIỆU (Đã gỡ bỏ GiamGia, thêm gia_trigiam)
    protected $casts = [
        'gia_trigiam' => 'Biginteger',
        'don_toithieu' => 'Biginteger',
        'giam_toida' => 'Biginteger',
        'Soluongma' => 'integer',
        'dasudung' => 'integer',
        'Ngaybdchuongtrinh' => 'datetime',
        'Ngayketthucchuongtrinh' => 'datetime',
    ];
    // 4. THIẾT LẬP CÁC MỐI QUAN HỆ ĐẦU RA
    /**
     * Mối quan hệ 1-Nhiều: Một mã khuyến mãi có thể áp dụng cho nhiều Đơn hàng
     */
    public function donHang()
    {
        return $this->hasMany(DonHang::class, 'MaKhuyenMai', 'id_khuyenmai');
    }
}
