<?php

namespace App\Models;

use Database\Factories\SanPhamSerialFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class sanpham_serials extends Model
{
    use HasFactory;

    protected $table      = 'sanpham_serials';
    protected $primaryKey = 'id_serial';

    protected $fillable = [
        'ma_tonkho', 'serial_code', 'tinhtrang', 'min_soluongkho', 'ngaycuthe',
    ];

    protected $casts = [
        'min_soluongkho' => 'integer',
        'ngaycuthe'      => 'datetime',
    ];

    protected static function newFactory(): SanPhamSerialFactory
    {
        return SanPhamSerialFactory::new();
    }

    public function tonKhoCucBo()
    {
        return $this->belongsTo(ton_kho_cuc_bo::class, 'ma_tonkho', 'id_khoton');
    }
}
