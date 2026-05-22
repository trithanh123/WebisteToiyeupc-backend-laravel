<?php
namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['Ten', 'email', 'matkhau', 'SDT', 'MaNCC', 'MaNCC_id', 'Phanquyen'])]
#[Hidden(['matkhau', 'remember_token'])]
class Nguoi_dung extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

   // Báo cho Laravel biết tên bảng mới
    protected $table = 'nguoi_dung';

    // Báo cho Laravel biết khóa chính mới
    protected $primaryKey = 'id_NguoiDung';

    // Xác định khóa chính tự động tăng
    public $incrementing = true;

    // Các trường được phép chèn dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        'Ten',
        'email',
        'matkhau',
        'SDT',
        'MaNCC',
        'MaNCC_id',
        'Phanquyen',
    ];

    // Các trường ẩn đi khi xuất dữ liệu API (để bảo mật)
    protected $hidden = [
        'matkhau',
        'remember_token',
    ];

    // Báo cho Laravel biết cột lưu mật khẩu đăng nhập tên là gì
    public function getAuthPasswordName()
    {
        return 'matkhau';
    }


    // 2. THIẾT LẬP CÁC MỐI QUAN HỆ (RELATIONSHIPS)
    /**
     * Mối quan hệ 1-1: Một người dùng có thể là một Nhân viên
     * (Trỏ tới model NhanVien qua khóa ngoại 'id_nguoidung')
     */
    public function nhanVien()
    {
        return $this->hasOne(NhanVien::class, 'id_nguoidung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ 1-Nhiều: Một người dùng có thể lưu nhiều địa chỉ nhận hàng
     */
    public function diaChi()
    {
        return $this->hasMany(DiaChiNguoiDung::class, 'id_nguoidung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ 1-Nhiều: Một người dùng có thể đặt nhiều Đơn hàng
     */
    public function donHang()
    {
        return $this->hasMany(DonHang::class, 'MaNguoiDung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ 1-Nhiều: Một người dùng có thể gửi nhiều yêu cầu Liên hệ
     */
    public function lienHe()
    {
        return $this->hasMany(LienHe::class, 'Ma_nguoidung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ 1-Nhiều: Một người dùng có thể để lại nhiều Đánh giá sản phẩm
     */
    public function danhGia()
    {
        return $this->hasMany(DanhGia::class, 'MaNguoiDung', 'id_NguoiDung');
    }
}
