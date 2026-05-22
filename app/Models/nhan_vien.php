<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class nhan_vien extends Model
{
    //cấu hình bảng
    protected $table = 'nhan_vien';
    protected $primaryKey = 'id_nhanvien';

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU (Đã loại bỏ cột email)
    protected $fillable = [
        'id_nguoidung',          
        'chucvu',
        'machinhanh',

    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ 1-1 (Inverse): Hồ sơ nhân viên này liên kết với 1 tài khoản đăng nhập
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoidung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ N-1: Nhân viên này làm việc và quản lý tại 1 Chi nhánh cụ thể
     * (Thầy dùng iD_ChiNhanh dựa trên file Model ChiNhanh mình đã làm trước đó)
     */
    public function chiNhanh()
    {
        return $this->belongsTo(ChiNhanh::class, 'machinhanh', 'iD_ChiNhanh');
    }
}
