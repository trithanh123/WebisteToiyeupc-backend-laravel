<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nhanvien', function (Blueprint $table) {
            $table->id('id_nhanvien'); // Khóa chính
            
            // Cột khóa ngoại trỏ về bảng nguoi_dung
            $table->unsignedBigInteger('id_nguoidung');
            
            $table->string('chucvu', 20)->nullable();
            
            // Trong ERD em để cột này là varchar(20) chứ không phải foreign key cứng
            $table->string('Machinhanhi', 20)->nullable(); 
            
            $table->timestamps();

            // Thiết lập ràng buộc khóa ngoại
            $table->foreign('id_nguoidung')
                  ->references('id_NguoiDung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhanvien');
    }
};