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
        Schema::create('nha_cung_cap', function (Blueprint $table) {
            $table->id('id_nhacungcap');
            $table->string('ten_nhacungcap', 200);
            $table->string('sdt', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('dia_chi', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('phieu_nhap_kho', function (Blueprint $table) {
            $table->id('id_phieunhap');
            $table->unsignedBigInteger('ma_nhacungcap');
            $table->unsignedBigInteger('ma_chinhanh');
            $table->date('ngaynhap');
            $table->enum('trang_thai', ['Đã nhập kho', 'Đã hủy'])->default('Đã nhập kho');
            $table->string('ghi_chu', 500)->nullable();
            $table->timestamps();

            $table->foreign('ma_nhacungcap')->references('id_nhacungcap')->on('nha_cung_cap')->onDelete('restrict');
            $table->foreign('ma_chinhanh')->references('id_chinhanh')->on('chi_nhanh')->onDelete('restrict');
        });

        Schema::create('chi_tiet_phieu_nhap', function (Blueprint $table) {
            $table->id('id_chitiet');
            $table->unsignedBigInteger('ma_phieunhap');
            $table->unsignedBigInteger('ma_sanpham');
            $table->integer('so_luong_nhap');
            $table->timestamps();

            $table->foreign('ma_phieunhap')->references('id_phieunhap')->on('phieu_nhap_kho')->onDelete('cascade');
            $table->foreign('ma_sanpham')->references('id_sanpham')->on('san_pham')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phieu_nhap');
        Schema::dropIfExists('phieu_nhap_kho');
        Schema::dropIfExists('nha_cung_cap');
    }
};
