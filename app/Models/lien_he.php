<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class lien_he extends Model
{
    use hasFactory;
    // cấu hình bảng
    protected $table = 'lien_he';
    protected $primaryKey = 'id'; // Em kiểm tra xem ERD là 'id' hay 'id_lienhe' nhé

    // 2. KHAI BÁO CÁC TRƯỜNG ĐƯỢC PHÉP CHÈN DỮ LIỆU
    // (Thành nhớ rà soát lại xem viết hoa hay viết thường có gạch dưới nha)
    protected $fillable = [
        'Ma_nguoidung', 
        'ten_lienhe',
        'email_lienhe',
        'SDT',
        'website',
        'noidung',
        'trangthai',
    ];

    // 3. ÉP KIỂU DỮ LIỆU
    protected $casts = [
        'Ma_nguoidung' => 'integer',
        'trangthai' => 'integer', // Thường dùng 0: Chưa xử lý, 1: Đã xử lý
    ];
    // 4. THIẾT LẬP MỐI QUAN HỆ KHÓA NGOẠI (BELONGS TO)
    /**
     * Mối quan hệ N-1: Một lượt gửi liên hệ có thể thuộc về 1 Người dùng đã đăng nhập,
     * hoặc bằng NULL nếu là khách vãng lai gửi câu hỏi bâng quơ.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'Ma_nguoidung', 'id_NguoiDung');
    }
}
