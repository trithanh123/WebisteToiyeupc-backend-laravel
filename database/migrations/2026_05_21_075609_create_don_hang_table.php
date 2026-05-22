<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('don_hang', function (Blueprint $table) {
           $table->unsignedBigInteger('id_DonHang', true);
            
            // 1. Các cột để chứa khóa ngoại
            $table->unsignedBigInteger('MaNguoiDung');
            $table->unsignedBigInteger('MaChiNhanh');
            $table->integer('MaKhuyenMai')->nullable(); // Để nullable vì khách có thể không xài mã giảm giá
            $table->unsignedBigInteger('MaDiachinguoidung');

            // 2. Các cột thông tin đơn hàng
            $table->bigInteger('TongTien');
            $table->string('Phuong_thuc_TT', 50);
            $table->string('Trang_thai_DH', 50);
            $table->text('ghichu')->nullable();
            
            // Cột thời gian đặt hàng 
            $table->timestamp('thoigiandathang')->useCurrent();
            
            $table->timestamps();

            // 3. Khai báo 4 ràng buộc khóa ngoại (Vì các bảng kia đã tạo xong nên giờ nối thoải mái)
            $table->foreign('MaNguoiDung')->references('id_NguoiDung')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('MaChiNhanh')->references('iD_ChiNhanh')->on('chi_nhanh')->onDelete('cascade');
            
            // Nếu mã khuyến mãi bị xóa, đơn hàng cũ vẫn giữ lại nhưng cột này sẽ thành null
            $table->foreign('MaKhuyenMai')->references('id_khuyenmai')->on('khuyen_mai')->onDelete('set null'); 
            
            $table->foreign('MaDiachinguoidung')->references('ID_DiaChiNguoiDung')->on('diachi_nguoidung')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_hang');
    }
};
