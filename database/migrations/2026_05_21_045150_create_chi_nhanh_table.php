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
        Schema::create('chi_nhanh', function (Blueprint $table) {
            // Khóa chính: iD_ChiNhanh (kiểu Bigint tự tăng)
            $table->id('iD_ChiNhanh'); 
            
            // Các cột thông tin chi nhánh
            $table->string('Ten_ChiNhanh', 255);
            $table->string('Ma_chi_nhanh', 50);
            $table->string('SDT_Chi_nhanh', 20)->nullable(); 
            $table->string('email_chi_nhanh', 255)->nullable();
            
            // Thông tin địa chỉ chi tiết
            $table->string('diachi_chitiet', 255);
            $table->integer('Maso_phuong')->nullable();
            $table->integer('Maso_TP')->nullable();
            $table->integer('Maso_TInh')->nullable();
            
            // Link bản đồ
            $table->string('map_link', 500)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_nhanh');
    }
};