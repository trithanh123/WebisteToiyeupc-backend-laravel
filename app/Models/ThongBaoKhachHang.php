<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBaoKhachHang extends Model
{
    use HasFactory;

    protected $table = 'thongbao_khachhang';
    protected $primaryKey = 'id_thongbao';

    protected $fillable = [
        'id_nguoidung',
        'loai_thong_bao',
        'tieu_de',
        'noi_dung',
        'da_doc',
        'link'
    ];

    public function user()
    {
        return $this->belongsTo(nguoi_dung::class, 'id_nguoidung', 'id_nguoidung');
    }
}
