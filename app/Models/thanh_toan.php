<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class thanh_toan extends Model
{
    use HasFactory;
    // cấu hình bảng
    // 1. CẤU HÌNH BẢNG
    protected $table = 'thanh_toan';
    protected $primaryKey = 'id_thanhtoan'; // Thành kiểm tra lại xem ERD là id_thanhtoan hay id_ThanhToan nha

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU
    protected $fillable = [
        'MaDonHang',
        'soluong',       
        'Ma_giaodich',     
        'SoTien',           
        'Phuong_thuc',   
        'trangthai',        
        'ngaythanhtoan'
    ];
    // 3. ÉP KIỂU DỮ LIỆU (Để tính toán và truy vấn dễ dàng hơn)
    protected $casts = [
        'soluong' => 'integer',
        'SoTien' => 'integer',
        'ngaythanhtoan' => 'datetime',
    ];

    // 4. THIẾT LẬP MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)
    /**
     * Mối quan hệ N-1: Một lượt thanh toán thuộc về 1 Đơn hàng
     */
    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'MaDonHang', 'id_DonHang');
    }
}
