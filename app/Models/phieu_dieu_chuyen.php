<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class phieu_dieu_chuyen extends Model
{
    protected $table = 'phieu_dieu_chuyen';
    protected $primaryKey = 'id_phieu';

    protected $fillable = [
        'ma_kho_xuat',
        'ma_kho_nhap',
        'nguoi_tao',
        'nguoi_duyet',
        'trang_thai',
        'ly_do',
        'ghi_chu',
    ];

    public function khoXuat()
    {
        return $this->belongsTo(chi_nhanh::class, 'ma_kho_xuat', 'id_chinhanh');
    }

    public function khoNhap()
    {
        return $this->belongsTo(chi_nhanh::class, 'ma_kho_nhap', 'id_chinhanh');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(Nguoi_dung::class, 'nguoi_tao', 'id_nguoidung');
    }

    public function nguoiDuyet()
    {
        return $this->belongsTo(Nguoi_dung::class, 'nguoi_duyet', 'id_nguoidung');
    }

    public function chiTiet()
    {
        return $this->hasMany(chi_tiet_dieu_chuyen::class, 'ma_phieu', 'id_phieu');
    }
}
