<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nhanvien', function (Blueprint $table) {
            $table->id('id_nhanvien');
            $table->unsignedBigInteger('id_nguoidung');
            $table->string('chucvu', 20)->nullable();
            $table->string('machinhanh', 20)->nullable();
            $table->timestamps();

            $table->foreign('id_nguoidung')
                  ->references('id_nguoidung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhanvien');
    }
};