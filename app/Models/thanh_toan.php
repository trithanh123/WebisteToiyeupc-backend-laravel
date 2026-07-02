<?php

namespace App\Models;

use Database\Factories\ThanhToanFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class thanh_toan extends Model
{
    use HasFactory;

    protected $table      = 'thanh_toan';
    protected $primaryKey = 'id_thanhtoan';

    protected $fillable = [
        'ma_donhang', 'soluong', 'phuong_thuc',
        'ma_giaodich', 'sotien', 'trangthai', 'ngaythanhtoan',
    ];

    protected $casts = [
        'soluong'       => 'integer',
        'sotien'        => 'integer',
        'ngaythanhtoan' => 'datetime',
    ];

    protected static function newFactory(): ThanhToanFactory
    {
        return ThanhToanFactory::new();
    }

    public function donHang()
    {
        return $this->belongsTo(don_hang::class, 'ma_donhang', 'id_donhang');
    }
}
