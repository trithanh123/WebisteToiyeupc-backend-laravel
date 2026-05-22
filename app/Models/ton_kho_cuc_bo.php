<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ton_kho_cuc_bo extends Model
{
    use HasFactory;
    //cấu hình bảng
    // 1. CẤU HÌNH BẢNG
    protected $table = 'ton_kho_cuc_bo';
    protected $primaryKey = 'ID_Khoton'; // Khóa chính của bảng tồn kho cục bộ

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU
    protected $fillable = [
        'MaSanPham',        // Khóa ngoại trỏ sang bảng san_pham
        'MaChiNhanh',       // Khóa ngoại trỏ sang bảng chi_nhanh
        'Soluong',          // Số lượng tồn kho thực tế tại chi nhánh này
        'Soluongkhothap',    // Ngưỡng cảnh báo khi sắp hết hàng (mặc định < 5)
    ];

    // 3. ÉP KIỂU DỮ LIỆU (Đảm bảo tính toán số lượng chính xác)
    protected $casts = [
        'MaSanPham' => 'BigInteger',
        'MaChiNhanh' => 'BigInteger',
        'Soluong' => 'integer',
        'Soluongkhothap' => 'integer',
    ];
    // 4. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ N-1: Bản ghi tồn kho này định danh cho 1 Sản phẩm cụ thể
     */
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'MaSanPham', 'ID_SanPham');
    }

    /**
     * Mối quan hệ N-1: Lượng tồn kho này đang nằm tại 1 Chi nhánh cụ thể
     */
    public function chiNhanh()
    {
        return $this->belongsTo(ChiNhanh::class, 'MaChiNhanh', 'iD_ChiNhanh');
    }

    // 5. THIẾT LẬP MỐI QUAN HỆ ĐẦU RA (HAS MANY)

    /**
     * Mối quan hệ 1-Nhiều: Một lô hàng tồn tại chi nhánh sẽ quản lý danh sách nhiều mã Số Serial cụ thể
     * (Phục vụ luồng tra cứu vị trí chính xác của từng con CPU, VGA dựa trên mã S/N)
     */
    public function serials()
    {
        return $this->hasMany(SanPhamSerial::class, 'MaTonKho', 'ID_Khoton');
    }
}
