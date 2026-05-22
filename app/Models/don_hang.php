<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class don_hang extends Model
{
    use HasFactory;
    // cấu hình bảng
    protected $table = 'don_hang';
    protected $primaryKey = 'id_donhang';
    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP THÊM DỮ LIỆU HÀNG LOẠT
    protected $fillable = [
        'MaNguoiDung',
        'MaChiNhanh',
        'MaKhuyenMai',
        'MaDiachinguoidung',
        'TongTien',
        'Phuong_thuc_TT',
        'Trang_thai_DH',
        'ghichu',
        'thoigiandathang',
    ];

    // Ép kiểu dữ liệu cho cột ngày đặt hàng và tổng tiền
    protected $casts = [
        'thoigiandathang' => 'datetime',
        'TongTien' => 'bigInteger',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ N-1: Đơn hàng thuộc về 1 Người dùng (Khách hàng)
     */
    public function user()
    {
        // Trỏ về Model User mặc định (đã được đổi sang bảng nguoi_dung)
        return $this->belongsTo(User::class, 'MaNguoiDung', 'id_NguoiDung');
    }

    /**
     * Mối quan hệ N-1: Đơn hàng được bán/xuất phát từ 1 Chi nhánh cửa hàng
     */
    public function chiNhanh()
    {
        return $this->belongsTo(ChiNhanh::class, 'MaChiNhanh', 'iD_ChiNhanh');
    }

    /**
     * Mối quan hệ N-1: Đơn hàng có thể áp dụng 1 Mã khuyến mãi
     */
    public function khuyenMai()
    {
        return $this->belongsTo(KhuyenMai::class, 'MaKhuyenMai', 'id_khuyenmai');
    }

    /**
     * Mối quan hệ N-1: Đơn hàng được gửi tới 1 Địa chỉ cụ thể của người dùng
     */
    public function diaChi()
    {
        return $this->belongsTo(DiaChiNguoiDung::class, 'MaDiachinguoidung', 'ID_DiaChiNguoiDung');
    }


    // 4. THIẾT LẬP CÁC QUAN HỆ ĐẦU RA (HAS MANY)

    /**
     * Mối quan hệ 1-Nhiều: Một đơn hàng sẽ bao gồm nhiều Chi tiết đơn hàng (các sản phẩm bên trong)
     */
    public function chiTietDonHang()
    {
        return $this->hasMany(ChiTietDonHang::class, 'MaDonHang', 'id_DonHang');
    }

    /**
     * Mối quan hệ 1-Nhiều: Một đơn hàng có thể có nhiều đợt/lịch sử Thanh toán
     */
    public function thanhToan()
    {
        return $this->hasMany(ThanhToan::class, 'MaDonHang', 'id_DonHang');
    }
}
