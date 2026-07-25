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
        Schema::create('chi_tiet_dieu_chuyen', function (Blueprint $table) {
            $table->id('id_chitiet');
            $table->unsignedBigInteger('ma_phieu');
            $table->unsignedBigInteger('ma_sanpham');
            $table->integer('so_luong');
            $table->timestamps();

            $table->foreign('ma_phieu')->references('id_phieu')->on('phieu_dieu_chuyen')->onDelete('cascade');
            $table->foreign('ma_sanpham')->references('id_sanpham')->on('san_pham')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_dieu_chuyen');
    }
};
