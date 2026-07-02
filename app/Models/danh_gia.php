<?php

namespace App\Models;

use Database\Factories\DanhGiaFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class danh_gia extends Model
{
    use HasFactory;

    protected $table      = 'danh_gia';
    protected $primaryKey = 'id_danhgia';

    protected $fillable = [
        'ma_nguoidung', 'ma_sanpham', 'danhgia', 'binhluan', 'thoigiantao',
    ];

    protected $casts = [
        'danhgia'    => 'integer',
        'thoigiantao' => 'datetime',
    ];

    protected static function newFactory(): DanhGiaFactory
    {
        return DanhGiaFactory::new();
    }

    public function nguoiDung()
    {
        return $this->belongsTo(Nguoi_dung::class, 'ma_nguoidung', 'id_nguoidung');
    }

    public function sanPham()
    {
        return $this->belongsTo(san_pham::class, 'ma_sanpham', 'id_sanpham');
    }
}
