<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ton_kho_cuc_bo', function (Blueprint $table) {
            $table->id('id_khoton');
            $table->unsignedBigInteger('ma_sanpham');
            $table->unsignedBigInteger('ma_chinhanh');
            $table->integer('soluongtonkho')->default(0);
            $table->integer('soluongkhothap')->default(5);
            $table->timestamps();
            $table->foreign('ma_sanpham')
                  ->references('id_sanpham')
                  ->on('san_pham')
                  ->onDelete('cascade');

            $table->foreign('ma_chinhanh')
                  ->references('id_chinhanh')
                  ->on('chi_nhanh')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ton_kho_cuc_bo');
    }
};
