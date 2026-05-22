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
       Schema::create('lien_he', function (Blueprint $table) {
            $table->integer('id')->autoIncrement(); // Khóa chính kiểu int
            
            // Khóa ngoại trỏ về bảng người dùng (cho phép null vì khách chưa đăng nhập cũng có thể gửi liên hệ)
            $table->unsignedBigInteger('Ma_nguoidung')->nullable();
            
            // Các cột thông tin
            $table->string('ten_lienhe', 20); // Theo ERD em để 20 ký tự
            $table->string('email_lienhe', 255);
            $table->string('SDT', 20); 
            $table->string('website', 20)->nullable();
            $table->text('noidung');
            
            // Cột trạng thái bổ sung kiểu dữ liệu
            $table->tinyInteger('trangthai')->default(0); 
            
            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('Ma_nguoidung')
                  ->references('id_NguoiDung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lien_he');
    }
};
