<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class san_pham extends Model
{
    // cấu hình bảng
    protected $table = 'san_pham';
    protected $primaryKey = 'ID_SanPham';

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP THÊM DỮ LIỆU
    protected $fillable = [
        'Ma_DanhMuc',
        'MaSP',
        'TenSP',
        'Gia',
        'Thumbail',
        'Motasanpham',
        'specifications',
        // 'embedding', // (Tạm thời đóng lại theo database hiện tại, mở ra sau khi cài Vector AI)
    ];

    // 3. ÉP KIỂU DỮ LIỆU (CASTS) - Tuyệt chiêu của Laravel
    protected $casts = [
        // Tự động chuyển đổi JSON trong Database thành mảng (Array) trong PHP và ngược lại
        'specifications' => 'array', 
        // Đảm bảo giá tiền luôn trả về số nguyên
        'Gia' => 'integer',
    ];
    // 4. THIẾT LẬP CÁC MỐI QUAN HỆ

    /**
     * Mối quan hệ N-1: Nhiều Sản phẩm thuộc về 1 Danh mục
     */
    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'Ma_DanhMuc', 'ID_DanhMuc');
    }

    /**
     * Mối quan hệ 1-Nhiều: 1 Sản phẩm sẽ nằm ở nhiều Kho cục bộ (các chi nhánh khác nhau)
     */
    public function tonKho()
    {
        return $this->hasMany(TonKhoCucBo::class, 'MaSanPham', 'ID_SanPham');
    }

    /**
     * Mối quan hệ 1-Nhiều: 1 Sản phẩm có thể xuất hiện trong nhiều Chi tiết đơn hàng
     */
    public function chiTietDonHang()
    {
        return $this->hasMany(ChiTietDonHang::class, 'MaSanPham', 'ID_SanPham');
    }

    /**
     * Mối quan hệ 1-Nhiều: 1 Sản phẩm có thể có nhiều Đánh giá
     */
    public function danhGia()
    {
        return $this->hasMany(DanhGia::class, 'MaSanPham', 'ID_SanPham');
    }
}
