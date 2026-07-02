<?php

namespace App\Models;

use Database\Factories\DiaChiNguoiDungFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class diachi_nguoidung extends Model
{
    use HasFactory;

    protected $table      = 'diachi_nguoidung';
    protected $primaryKey = 'id_diachinguoidung';

    protected $fillable = [
        'id_nguoidung', 'ten_nguoinhan', 'sdt_nguoinhan',
        'ma_thanhpho', 'ma_quan', 'ma_phuong',
        'diachi_chitiet', 'matudien_diachi',
    ];

    protected $casts = [
        'matudien_diachi' => 'boolean',
        'ma_thanhpho'     => 'integer',
        'ma_quan'         => 'integer',
        'ma_phuong'       => 'integer',
    ];

    protected static function newFactory(): DiaChiNguoiDungFactory
    {
        return DiaChiNguoiDungFactory::new();
    }

    public function nguoiDung()
    {
        return $this->belongsTo(Nguoi_dung::class, 'id_nguoidung', 'id_nguoidung');
    }

    public function donHang()
    {
        return $this->hasMany(don_hang::class, 'ma_diachinguoidung', 'id_diachinguoidung');
    }
}
