<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sanpham_serials extends Model
{
    use HasFactory;
    //cấu hình bảng
    protected $table = 'sanpham_serials';
    protected $primaryKey = 'ID_Serial';
    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP THÊM DỮ LIỆU
    protected $fillable = [
        'MaTonKho',
        'serial_code',
        'tinhtrang',
        'Min_Soluongkho',
        'Ngaycuthe',
    ];
    // Ép kiểu dữ liệu để dễ dàng thao tác tính toán và định dạng ngày tháng
    protected $casts = [
        'Min_Soluongkho' => 'integer',
        'Ngaycuthe' => 'datetime',
    ];
    // 3. THIẾT LẬP CÁC MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)

    /**
     * Mối quan hệ N-1: Một mã Serial cụ thể sẽ nằm trong một lô Tồn kho cục bộ 
     * (Từ đây có thể truy ngược ra mã Serial này đang ở Chi nhánh nào và thuộc Sản phẩm gì)
     */
    public function tonKhoCucBo()
    {
        return $this->belongsTo(TonKhoCucBo::class, 'MaTonKho', 'ID_Khoton');
    }
}
