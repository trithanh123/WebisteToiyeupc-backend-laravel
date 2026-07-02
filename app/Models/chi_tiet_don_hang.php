<?php

namespace App\Models;

use Database\Factories\ChiTietDonHangFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class chi_tiet_don_hang extends Model
{
    use HasFactory;

    protected $table      = 'chi_tiet_don_hang';
    protected $primaryKey = 'id_chitietdh';

    protected $fillable = [
        'ma_donhang', 'ma_sanpham', 'soluong', 'don_gia', 'thanh_tien',
    ];

    protected $casts = [
        'soluong'    => 'integer',
        'don_gia'    => 'integer',
        'thanh_tien' => 'integer',
    ];

    protected static function newFactory(): ChiTietDonHangFactory
    {
        return ChiTietDonHangFactory::new();
    }

    public function donHang()
    {
        return $this->belongsTo(don_hang::class, 'ma_donhang', 'id_donhang');
    }

    public function sanPham()
    {
        return $this->belongsTo(san_pham::class, 'ma_sanpham', 'id_sanpham');
    }
}
