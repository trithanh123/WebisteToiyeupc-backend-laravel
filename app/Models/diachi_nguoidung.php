<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class diachi_nguoidung extends Model
{
    use HasFactory;
    // 1. CẤU HÌNH BẢNG (Khớp với khóa chính và tên bảng đã liên kết ở model User/DonHang)
    protected $table = 'diachi_nguoidung';
    protected $primaryKey = 'ID_DiaChiNguoiDung';

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU HÀNG LOẠT
    protected $fillable = [
        'id_nguoidung',
        'Ten_NguoiNhan',
        'SDT_NguoiNhan',
        'DiaChi_ChiTiet',
        'is_default',
    ];

    // Ép kiểu dữ liệu cho trạng thái địa chỉ mặc định
    protected $casts = [
        'is_default' => 'boolean',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ N-1: Địa chỉ thuộc về một Người dùng (Khách hàng) cụ thể
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoidung', 'id_NguoiDung');
    }

    // 4. THIẾT LẬP CÁC QUAN HỆ ĐẦU RA (HAS MANY)

    /**
     * Mối quan hệ 1-Nhiều: Một địa chỉ lưu sẵn có thể được áp dụng vào nhiều Đơn hàng khác nhau khi đặt mua
     */
    public function donHang()
    {
        return $this->hasMany(DonHang::class, 'MaDiachinguoidung', 'ID_DiaChiNguoiDung');
    }
}
