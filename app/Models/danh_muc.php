<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class danh_muc extends Model
{
    //cấu hình bảng
    protected $table = 'danh_muc';
    protected $primaryKey = 'ID_DanhMuc';
    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP THÊM DỮ LIỆU HÀNG LOẠT
    protected $fillable = [
        'Ten_DanhMuc',
        'slug',
        'DanhMuc_cha',
        'Hinhanh_icon',
        'is_active',
    ];

    // Ép kiểu dữ liệu cho trạng thái kích hoạt
    protected $casts = [
        'is_active' => 'boolean',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ (RELATIONSHIPS)

    /**
     * Mối quan hệ 1-Nhiều: 1 Danh mục có thể chứa nhiều Sản phẩm
     * (Móc nối ngược lại với trường Ma_DanhMuc ở bảng san_pham)
     */
    public function sanPham()
    {
        return $this->hasMany(SanPham::class, 'Ma_DanhMuc', 'ID_DanhMuc');
    }

    /**
     * QUAN HỆ ĐỆ QUY (BbelongsTo): Danh mục con trỏ ngược về Danh mục cha của nó
     */
    public function danhMucCha()
    {
        return $this->belongsTo(DanhMuc::class, 'DanhMuc_cha', 'ID_DanhMuc');
    }

    /**
     * QUAN HỆ ĐỆ QUY (HasMany): Từ Danh mục cha lấy ra danh sách các Danh mục con bên trong
     */
    public function danhMucCon()
    {
        return $this->hasMany(DanhMuc::class, 'DanhMuc_cha', 'ID_DanhMuc');
    }
}
