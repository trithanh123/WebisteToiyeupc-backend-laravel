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
       Schema::create('ton_kho_cuc_bo', function (Blueprint $table) {
            $table->id('ID_Khoton'); // Khóa chính Bigint
            
            // Các cột khóa ngoại (unsignedBigInteger để khớp với bảng gốc)
            $table->unsignedBigInteger('MaSanPham');
            $table->unsignedBigInteger('MaChiNhanh');
            // Thông tin số lượng tồn kho
            $table->integer('Soluongtonkho')->default(0);
            $table->integer('Soluongkhothap')->default(5);
            $table->timestamps();
            // Ràng buộc khóa ngoại
            $table->foreign('MaSanPham')
                  ->references('ID_SanPham')
                  ->on('san_pham')
                  ->onDelete('cascade');
            $table->foreign('MaChiNhanh')
                  ->references('iD_ChiNhanh')
                  ->on('chi_nhanh')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ton_kho_cuc_bo');
    }
};
