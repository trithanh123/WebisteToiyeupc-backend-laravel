<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_hang', function (Blueprint $table) {
            $table->id('id_donhang');
            $table->unsignedBigInteger('ma_nguoidung');
            $table->unsignedBigInteger('ma_chinhanh');
            $table->integer('ma_khuyenmai')->nullable();
            $table->unsignedBigInteger('ma_diachinguoidung');
            $table->bigInteger('tongtien');
            $table->string('phuong_thuc_tt', 50);
            $table->string('trang_thai_dh', 50);
            $table->text('ghichu')->nullable();
            $table->timestamp('thoigiandathang')->useCurrent();
            $table->timestamps();

            $table->foreign('ma_nguoidung')->references('id_nguoidung')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('ma_chinhanh')->references('id_chinhanh')->on('chi_nhanh')->onDelete('cascade');
            $table->foreign('ma_khuyenmai')->references('id_khuyenmai')->on('khuyen_mai')->onDelete('set null');
            $table->foreign('ma_diachinguoidung')->references('id_diachinguoidung')->on('diachi_nguoidung')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_hang');
    }
};
