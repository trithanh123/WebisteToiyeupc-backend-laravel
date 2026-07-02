<?php

namespace App\Models;

use Database\Factories\DonHangFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class don_hang extends Model
{
    use HasFactory;

    protected $table      = 'don_hang';
    protected $primaryKey = 'id_donhang';

    protected $fillable = [
        'ma_nguoidung', 'ma_chinhanh', 'ma_khuyenmai',
        'ma_diachinguoidung', 'tongtien',
        'phuong_thuc_tt', 'trang_thai_dh',
        'ghichu', 'thoigiandathang',
    ];

    protected $casts = [
        'thoigiandathang' => 'datetime',
        'tongtien'        => 'integer',
    ];

    protected static function newFactory(): DonHangFactory
    {
        return DonHangFactory::new();
    }

    public function nguoiDung()
    {
        return $this->belongsTo(Nguoi_dung::class, 'ma_nguoidung', 'id_nguoidung');
    }

    public function chiNhanh()
    {
        return $this->belongsTo(chi_nhanh::class, 'ma_chinhanh', 'id_chinhanh');
    }

    public function khuyenMai()
    {
        return $this->belongsTo(khuyen_mai::class, 'ma_khuyenmai', 'id_khuyenmai');
    }

    public function diaChi()
    {
        return $this->belongsTo(diachi_nguoidung::class, 'ma_diachinguoidung', 'id_diachinguoidung');
    }

    public function chiTietDonHang()
    {
        return $this->hasMany(chi_tiet_don_hang::class, 'ma_donhang', 'id_donhang');
    }

    public function thanhToan()
    {
        return $this->hasMany(thanh_toan::class, 'ma_donhang', 'id_donhang');
    }
}
