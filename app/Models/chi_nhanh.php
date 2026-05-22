<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class chi_nhanh extends Model
{
    use HasFactory;
    // 1. CẤU HÌNH BẢNG (Lưu ý chữ iD_ChiNhanh viết hoa đúng chuẩn migration)
    protected $table = 'chi_nhanh';
    protected $primaryKey = 'iD_ChiNhanh';
    public $timestamps = true;
    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU
    protected $fillable = [
        'Ten_ChiNhanh',
        'Ma_chi_nhanh',
        'SDT_Chi_nhanh',
        'email_chi_nhanh',
        'diachi_chitiet',
        'Maso_phuong',
        'Maso_TP',
        'Maso_TInh',
        'map_link',
    ];
    // Ép kiểu dữ liệu cho các mã số định danh khu vực hành chính
    protected $casts = [
        'Maso_phuong' => 'integer',
        'Maso_TP' => 'integer',
        'Maso_TInh' => 'integer',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ ĐẦU RA (HAS MANY)

    /**
     * Mối quan hệ 1-Nhiều: Một chi nhánh sẽ chứa một kho hàng tồn cục bộ riêng biệt
     */
    public function tonKho()
    {
        return $this->hasMany(TonKhoCucBo::class, 'MaChiNhanh', 'iD_ChiNhanh');
    }

    /**
     * Mối quan hệ 1-Nhiều: Một chi nhánh có thể tiếp nhận và xử lý nhiều Đơn hàng
     */
    public function donHang()
    {
        return $this->hasMany(DonHang::class, 'MaChiNhanh', 'iD_ChiNhanh');
    }
}
