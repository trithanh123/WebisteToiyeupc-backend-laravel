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
       Schema::create('chi_tiet_don_hang', function (Blueprint $table) {
            $table->id('id_ChiTietDH'); // Khóa chính
            
            // Các cột khóa ngoại
            $table->unsignedBigInteger('MaDonHang');
            $table->unsignedBigInteger('MaSanPham');
            
            // Thông tin chi tiết mua hàng
            $table->integer('Soluong');
            $table->bigInteger('Don_gia');
            $table->bigInteger('Thanh_tien');
            
            $table->timestamps();

            // Móc khóa ngoại an toàn
            $table->foreign('MaDonHang')
                  ->references('id_DonHang')
                  ->on('don_hang')
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
        Schema::dropIfExists('chi_tiet_don_hang');
    }
};
