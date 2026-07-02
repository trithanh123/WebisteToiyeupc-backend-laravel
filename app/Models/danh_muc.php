<?php
namespace App\Models;
use Database\Factories\DanhMucFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class danh_muc extends Model
{
    use HasFactory;
    protected $table      = 'danh_muc';
    protected $primaryKey = 'id_danhmuc';
    protected $fillable = [
        'ten_danhmuc', 'slug', 'danhmuc_cha', 'hinhanh_icon', 'is_active',
    ];
    protected $casts = [
        'is_active'   => 'boolean',
        'danhmuc_cha' => 'integer',
    ];
    protected static function newFactory(): DanhMucFactory
    {
        return DanhMucFactory::new();
    }
    public function danhMucCha()
    {
        return $this->belongsTo(danh_muc::class, 'danhmuc_cha', 'id_danhmuc');
    }
    public function danhMucCon()
    {
        return $this->hasMany(danh_muc::class, 'danhmuc_cha', 'id_danhmuc');
    }
    public function conVaChau()
    {
        return $this->hasMany(danh_muc::class, 'danhmuc_cha', 'id_danhmuc')
                    ->with('conVaChau');  
    }
    public function laDanhMucGoc(): bool
    {
        return is_null($this->danhmuc_cha);
    }
    public function laDanhMucLa(): bool
    {
        return $this->danhMucCon()->doesntExist();
    }
    public function sanPham()
    {
        return $this->hasMany(san_pham::class, 'ma_danhmuc', 'id_danhmuc');
    }
}
