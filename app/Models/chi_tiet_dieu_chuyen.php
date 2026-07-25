<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class chi_tiet_dieu_chuyen extends Model
{
    protected $table = 'chi_tiet_dieu_chuyen';
    protected $primaryKey = 'id_chitiet';

    protected $fillable = [
        'ma_phieu',
        'ma_sanpham',
        'so_luong',
    ];

    public function phieu()
    {
        return $this->belongsTo(phieu_dieu_chuyen::class, 'ma_phieu', 'id_phieu');
    }

    public function sanPham()
    {
        return $this->belongsTo(san_pham::class, 'ma_sanpham', 'id_sanpham');
    }

    public function serials()
    {
        return $this->hasMany(dieu_chuyen_serials::class, 'ma_chitiet', 'id_chitiet');
    }
}
