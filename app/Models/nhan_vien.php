<?php
namespace App\Models;
use Database\Factories\NhanVienFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class nhan_vien extends Model
{
    use HasFactory;
    protected $table      = 'nhanvien'; 
    protected $primaryKey = 'id_nhanvien';
    protected $fillable = ['id_nguoidung', 'chucvu', 'machinhanh'];
    protected $casts = [
        'id_nguoidung' => 'integer',
        'machinhanh'   => 'integer',
    ];
    protected static function newFactory(): NhanVienFactory
    {
        return NhanVienFactory::new();
    }
    public function nguoiDung()
    {
        return $this->belongsTo(Nguoi_dung::class, 'id_nguoidung', 'id_nguoidung');
    }
    public function chiNhanh()
    {
        return $this->belongsTo(chi_nhanh::class, 'machinhanh', 'id_chinhanh');
    }
}
