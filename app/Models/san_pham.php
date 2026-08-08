<?php
namespace App\Models;
use Database\Factories\SanPhamFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class san_pham extends Model
{
    use HasFactory;
    protected $table      = 'san_pham';
    protected $primaryKey = 'id_sanpham';
    protected $fillable = [
        'ma_danhmuc', 'masp', 'tensp', 'gia',
        'thumbail', 'motasanpham', 'specifications', 'embedding',
    ];
    protected $hidden = ['embedding'];
    protected $casts = [
        'specifications' => 'array',
        'gia'            => 'integer',
        'ma_danhmuc'     => 'integer',
    ];
    public function serials()
    {
        return $this->hasManyThrough(
            sanpham_serials::class,
            ton_kho_cuc_bo::class,
            'ma_sanpham', 
            'ma_tonkho',  
            'id_sanpham',
            'id_khoton'
        );
    }
    protected static function newFactory(): SanPhamFactory
    {
        return SanPhamFactory::new();
    }
    public function danhMuc()
    {
        return $this->belongsTo(danh_muc::class, 'ma_danhmuc', 'id_danhmuc');
    }
    public function tonKho()
    {
        return $this->hasMany(ton_kho_cuc_bo::class, 'ma_sanpham', 'id_sanpham');
    }
    public function chiTietDonHang()
    {
        return $this->hasMany(chi_tiet_don_hang::class, 'ma_sanpham', 'id_sanpham');
    }
    public function danhGia()
    {
        return $this->hasMany(danh_gia::class, 'ma_sanpham', 'id_sanpham');
    }
}
