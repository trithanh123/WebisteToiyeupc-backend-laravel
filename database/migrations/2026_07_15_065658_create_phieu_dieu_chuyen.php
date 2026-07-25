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
        Schema::create('phieu_dieu_chuyen', function (Blueprint $table) {
            $table->id('id_phieu');
            $table->unsignedBigInteger('ma_kho_xuat');
            $table->unsignedBigInteger('ma_kho_nhap');
            $table->unsignedBigInteger('nguoi_tao');
            $table->unsignedBigInteger('nguoi_duyet')->nullable();
            $table->enum('trang_thai', ['Chờ duyệt', 'Đang vận chuyển', 'Hoàn thành', 'Từ chối', 'Đã hủy'])->default('Chờ duyệt');
            $table->string('ly_do')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
            
            $table->foreign('ma_kho_xuat')->references('id_chinhanh')->on('chi_nhanh')->onDelete('cascade');
            $table->foreign('ma_kho_nhap')->references('id_chinhanh')->on('chi_nhanh')->onDelete('cascade');
            $table->foreign('nguoi_tao')->references('id_nguoidung')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('nguoi_duyet')->references('id_nguoidung')->on('nguoi_dung')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phieu_dieu_chuyen');
    }
};
