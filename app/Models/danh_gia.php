<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class danh_gia extends Model
{
    use HasFactory;
    // cấu hình bảng
    protected $table = 'danh_gia';
    protected $primaryKey = 'id_DanhGia';

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU
    protected $fillable = [
        'MaNguoiDung',
        'MaSanPham',
        'Danhgia',
        'Binhluan',
        'thoigiantao',
    ];

    // Ép kiểu dữ liệu để dễ dàng thao tác tính toán số sao và định dạng ngày giờ
    protected $casts = [
        'Danhgia' => 'integer',
        'thoigiantao' => 'datetime',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ N-1: Một đánh giá là do 1 Người dùng (Khách hàng) viết ra
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'MaNguoiDung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ N-1: Một đánh giá là dành cho 1 Sản phẩm cụ thể
     */
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'MaSanPham', 'ID_SanPham');
    }
}
