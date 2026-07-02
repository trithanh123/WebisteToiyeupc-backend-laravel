<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_gia', function (Blueprint $table) {
            $table->integer('id_danhgia')->autoIncrement();
            $table->unsignedBigInteger('ma_nguoidung');
            $table->unsignedBigInteger('ma_sanpham');
            $table->integer('danhgia');
            $table->text('binhluan')->nullable();
            $table->dateTime('thoigiantao')->useCurrent();
            $table->timestamps();

            $table->foreign('ma_nguoidung')
                  ->references('id_nguoidung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');

            $table->foreign('ma_sanpham')
                  ->references('id_sanpham')
                  ->on('san_pham')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_gia');
    }
};
