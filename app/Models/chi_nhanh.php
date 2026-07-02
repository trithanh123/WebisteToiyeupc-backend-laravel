<?php
namespace App\Models;
use Database\Factories\ChiNhanhFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class chi_nhanh extends Model
{
    use HasFactory;
    protected $table = 'chi_nhanh';
    protected $primaryKey = 'id_chinhanh';
    public $timestamps = true;
    protected $fillable = [
        'ten_chinhanh',
        'ma_chinhanh',
        'sdt_chinhanh',
        'email_chinhanh',
        'diachi_chitiet',
        'maso_phuong',
        'maso_tp',
        'maso_tinh',
        'map_link',
    ];
    protected $casts = [
        'maso_phuong' => 'integer',
        'maso_tp'     => 'integer',
        'maso_tinh'   => 'integer',
    ];
    protected static function newFactory(): ChiNhanhFactory
    {
        return ChiNhanhFactory::new();
    }
    public function tonKho()
    {
        return $this->hasMany(ton_kho_cuc_bo::class, 'ma_chinhanh', 'id_chinhanh');
    }
    public function donHang()
    {
        return $this->hasMany(don_hang::class, 'ma_chinhanh', 'id_chinhanh');
    }
}
