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
      Schema::create('danh_gia', function (Blueprint $table) {
            $table->integer('id_danhgia')->autoIncrement(); // Khóa chính
            
            // Các khóa ngoại
            $table->unsignedBigInteger('MaNguoiDung');
            $table->unsignedBigInteger('MaSanPham');
            
            $table->integer('Danhgia');
            $table->text('Binhluan')->nullable(); 
            
            $table->dateTime('thoigiantao')->useCurrent();
            
            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('MaNguoiDung')
                  ->references('id_NguoiDung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
                  
            $table->foreign('MaSanPham')
                  ->references('ID_SanPham')
                  ->on('san_pham')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gia');
    }
};
