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
       Schema::create('diachi_nguoidung', function (Blueprint $table) {
            $table->id('id_diachinguoidung'); 
            $table->unsignedBigInteger('id_nguoidung');
            $table->string('ten_nguoinhan', 255);
            $table->string('sdt_nguoinhan', 20); 
            $table->integer('ma_thanhpho')->nullable();
            $table->integer('ma_quan')->nullable();
            $table->integer('ma_phuong')->nullable();
            $table->string('diachi_chitiet', 255);
            $table->boolean('Matudien_diachi')->default(false); 
            $table->timestamps();

            $table->foreign('id_nguoidung')
                  ->references('id_NguoiDung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diachi_nguoidung');
    }
};
