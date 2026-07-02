<?php
namespace App\Models;
use Database\Factories\NguoiDungFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Nguoi_dung extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table      = 'nguoi_dung';
    protected $primaryKey = 'id_nguoidung';
    public $incrementing  = true;
    protected $fillable = [
        'ten', 'email', 'matkhau', 'sdt',
        'ngaysinh', 'gioitinh',
        'mancc', 'mancc_id', 'avatar', 'phanquyen',
        'email_verified_at',
    ];
    protected $hidden = ['matkhau', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phanquyen'         => 'integer',
    ];
    protected static function newFactory(): NguoiDungFactory
    {
        return NguoiDungFactory::new();
    }
    public function getAuthPasswordName(): string
    {
        return 'matkhau';
    }
    public function nhanVien()
    {
        return $this->hasOne(nhan_vien::class, 'id_nguoidung', 'id_nguoidung');
    }
    public function diaChi()
    {
        return $this->hasMany(diachi_nguoidung::class, 'id_nguoidung', 'id_nguoidung');
    }
    public function donHang()
    {
        return $this->hasMany(don_hang::class, 'ma_nguoidung', 'id_nguoidung');
    }
    public function lienHe()
    {
        return $this->hasMany(lien_he::class, 'ma_nguoidung', 'id_nguoidung');
    }
    public function danhGia()
    {
        return $this->hasMany(danh_gia::class, 'ma_nguoidung', 'id_nguoidung');
    }
}
