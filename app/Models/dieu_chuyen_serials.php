<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dieu_chuyen_serials extends Model
{
    protected $table = 'dieu_chuyen_serials';
    protected $primaryKey = 'id_dieu_chuyen_serial';

    protected $fillable = [
        'ma_chitiet',
        'ma_serial',
    ];

    public function chiTiet()
    {
        return $this->belongsTo(chi_tiet_dieu_chuyen::class, 'ma_chitiet', 'id_chitiet');
    }

    public function serial()
    {
        return $this->belongsTo(sanpham_serials::class, 'ma_serial', 'id_serial');
    }
}
