<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaoHanh_HoTro extends Model
{
    protected $table      = 'baohanh_hotro';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ma_donhang',
        'ma_nguoidung',
        'ma_nhanvien',
        'ma_chinhanh',
        'ma_serial',
        'loai_yeu_cau',
        'mo_ta_loi',
        'trang_thai',
        'ket_qua_xu_ly',
        'ngay_tiep_nhan',
        'ngay_hoan_thanh',
    ];

    protected $casts = [
        'ngay_tiep_nhan'  => 'datetime',
        'ngay_hoan_thanh' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function donHang()
    {
        return $this->belongsTo(don_hang::class, 'ma_donhang', 'id_donhang');
    }

    public function nguoiDung()
    {
        return $this->belongsTo(Nguoi_dung::class, 'ma_nguoidung', 'id_nguoidung');
    }

    public function nhanVien()
    {
        return $this->belongsTo(nhan_vien::class, 'ma_nhanvien', 'id_nhanvien');
    }

    public function chiNhanh()
    {
        return $this->belongsTo(chi_nhanh::class, 'ma_chinhanh', 'id_chinhanh');
    }

    public function serial()
    {
        return $this->belongsTo(sanpham_serials::class, 'ma_serial', 'id_serial');
    }
}
