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
       Schema::create('diachi_nguoidung', function (Blueprint $table) {
            $table->id('ID_DiaChiNguoiDung'); // Khóa chính Bigint
            // Khóa ngoại trỏ về bảng người dùng
            $table->unsignedBigInteger('id_nguoidung');
            $table->string('tennguoinhan', 255);
            $table->string('SDT_nguoinhan', 20); // Đã bỏ dấu tiếng Việt để an toàn
            $table->integer('Ma_ThanhPho')->nullable();
            $table->integer('Ma_Quan')->nullable();
            $table->integer('Ma_Phuong')->nullable();
            $table->string('Diachi_chitiet', 255);
            // Cột lưu trạng thái địa chỉ mặc định (true/false)
            $table->boolean('Matudien_diachi')->default(false); 
            $table->timestamps();
            // Khai báo ràng buộc khóa ngoại
            $table->foreign('id_nguoidung')
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
        Schema::dropIfExists('diachi_nguoidung');
    }
};
