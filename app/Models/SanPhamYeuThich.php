<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanPhamYeuThich extends Model
{
    use HasFactory;

    protected $table = 'sanpham_yeuthich';

    protected $fillable = [
        'id_nguoidung',
        'id_sanpham'
    ];

    public function product()
    {
        return $this->belongsTo(san_pham::class, 'id_sanpham', 'id_sanpham');
    }
}
