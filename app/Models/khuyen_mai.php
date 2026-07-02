<?php

namespace App\Models;

use Database\Factories\KhuyenMaiFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class khuyen_mai extends Model
{
    use HasFactory;

    protected $table      = 'khuyen_mai';
    protected $primaryKey = 'id_khuyenmai';

    protected static function newFactory(): KhuyenMaiFactory
    {
        return KhuyenMaiFactory::new();
    }

    protected $fillable = [
        'ma_voucher', 'tenkhuyenmai', 'loai_giamgia',
        'gia_trigiam', 'don_toithieu', 'giam_toida',
        'soluongma', 'dasudung',
        'ngaybdchuongtrinh', 'ngayketthucchuongtrinh',
    ];

    protected $casts = [
        'gia_trigiam'             => 'integer',
        'don_toithieu'            => 'integer',
        'giam_toida'              => 'integer',
        'soluongma'               => 'integer',
        'dasudung'                => 'integer',
        'ngaybdchuongtrinh'       => 'datetime',
        'ngayketthucchuongtrinh'  => 'datetime',
    ];

    public function donHang()
    {
        return $this->hasMany(don_hang::class, 'ma_khuyenmai', 'id_khuyenmai');
    }
}
