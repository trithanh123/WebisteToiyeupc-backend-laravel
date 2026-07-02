<?php

namespace App\Models;

use Database\Factories\TonKhoCucBoFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ton_kho_cuc_bo extends Model
{
    use HasFactory;

    protected $table      = 'ton_kho_cuc_bo';
    protected $primaryKey = 'id_khoton';

    protected $fillable = [
        'ma_sanpham', 'ma_chinhanh', 'soluongtonkho', 'soluongkhothap',
    ];

    protected $casts = [
        'ma_sanpham'     => 'integer',
        'ma_chinhanh'    => 'integer',
        'soluongtonkho'  => 'integer',
        'soluongkhothap' => 'integer',
    ];

    protected static function newFactory(): TonKhoCucBoFactory
    {
        return TonKhoCucBoFactory::new();
    }

    public function sanPham()
    {
        return $this->belongsTo(san_pham::class, 'ma_sanpham', 'id_sanpham');
    }

    public function chiNhanh()
    {
        return $this->belongsTo(chi_nhanh::class, 'ma_chinhanh', 'id_chinhanh');
    }

    public function serials()
    {
        return $this->hasMany(sanpham_serials::class, 'ma_tonkho', 'id_khoton');
    }
}
