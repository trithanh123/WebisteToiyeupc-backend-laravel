<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDonHangSerial extends Model
{
    protected $table= 'chi_tiet_don_hang__serial';
    protected $fillable = ['ma_chitietdh','ma_serial'];

    public function chiTietDonHang()
    {
        return $this->belongsTo(chi_tiet_don_hang::class, 'ma_chitietdh', 'id_chitietdh');
    }

    public function sanPhamSerial()
    {
        return $this->belongsTo(sanpham_serials::class, 'ma_serial', 'id_serial');
    }
}
