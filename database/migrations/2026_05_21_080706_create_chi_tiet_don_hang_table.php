<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_don_hang', function (Blueprint $table) {
            $table->id('id_chitietdh');
            $table->unsignedBigInteger('ma_donhang');
            $table->unsignedBigInteger('ma_sanpham');
            $table->integer('soluong');
            $table->bigInteger('don_gia');
            $table->bigInteger('thanh_tien');
            $table->timestamps();

            $table->foreign('ma_donhang')
                  ->references('id_donhang')
                  ->on('don_hang')
                  ->onDelete('cascade');

            $table->foreign('ma_sanpham')
                  ->references('id_sanpham')
                  ->on('san_pham')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_don_hang');
    }
};
