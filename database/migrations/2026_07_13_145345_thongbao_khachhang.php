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
        Schema::create('thongbao_khachhang', function (Blueprint $table) {
            $table->id('id_thongbao');
            $table->unsignedBigInteger('id_nguoidung');
            $table->string('loai_thong_bao', 50)->default('don_hang'); // 'uu_dai' or 'don_hang'
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->boolean('da_doc')->default(false);
            $table->string('link')->nullable();
            $table->timestamps();

            $table->foreign('id_nguoidung')
                  ->references('id_nguoidung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thongbao_khachhang');
    }
};
