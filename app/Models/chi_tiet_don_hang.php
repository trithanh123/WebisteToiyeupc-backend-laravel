<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class chi_tiet_don_hang extends Model
{
    use hasFactory;
    // cấu hình bảng
   
    protected $table = 'chi_tiet_don_hang';
    protected $primaryKey = 'id_ChiTietDH';

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP THÊM DỮ LIỆU
    protected $fillable = [
        'MaDonHang',
        'MaSanPham',
        'Soluong',
        'Don_gia',
        'Thanh_tien',
    ];

    // Ép kiểu dữ liệu để đảm bảo các phép tính toán tiền bạc/số lượng luôn chính xác
    protected $casts = [
        'Soluong' => 'integer',
        'Don_gia' => 'bigInteger',
        'Thanh_tien' => 'bigInteger',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ N-1: Chi tiết này thuộc về một Đơn hàng cụ thể
     */
    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'MaDonHang', 'id_DonHang');
    }

    /**
     * Mối quan hệ N-1: Chi tiết này định danh cho một Sản phẩm (Linh kiện) cụ thể
     */
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'MaSanPham', 'ID_SanPham');
    }
}
