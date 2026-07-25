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
        Schema::create('sanpham_yeuthich', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_nguoidung');
            $table->unsignedBigInteger('id_sanpham');
            $table->timestamps();

            $table->foreign('id_nguoidung')->references('id_nguoidung')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('id_sanpham')->references('id_sanpham')->on('san_pham')->onDelete('cascade');
            
            // Một người dùng chỉ được yêu thích một sản phẩm 1 lần
            $table->unique(['id_nguoidung', 'id_sanpham']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanpham_yeuthich');
    }
};
