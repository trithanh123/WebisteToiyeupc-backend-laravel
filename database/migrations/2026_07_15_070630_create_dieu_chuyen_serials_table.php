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
        Schema::create('dieu_chuyen_serials', function (Blueprint $table) {
            $table->id('id_dieu_chuyen_serial');
            $table->unsignedBigInteger('ma_chitiet');
            $table->unsignedBigInteger('ma_serial');
            $table->timestamps();

            $table->foreign('ma_chitiet')->references('id_chitiet')->on('chi_tiet_dieu_chuyen')->onDelete('cascade');
            $table->foreign('ma_serial')->references('id_serial')->on('sanpham_serials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dieu_chuyen_serials');
    }
};
