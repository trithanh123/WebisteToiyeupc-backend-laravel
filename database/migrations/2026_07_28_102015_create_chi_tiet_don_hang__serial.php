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
        Schema::create('chi_tiet_don_hang__serial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_chitietdh');
            $table->unsignedBigInteger('ma_serial');
            $table->timestamps();

            $table->foreign('ma_chitietdh')->references('id_chitietdh')->on('chi_tiet_don_hang')->onDelete('cascade');
            $table->foreign('ma_serial')->references('id_serial')->on('sanpham_serials')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_don_hang__serial');
    }
};
