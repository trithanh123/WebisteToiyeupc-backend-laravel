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
        Schema::create('khuyen_mai', function (Blueprint $table) {
            // Khóa chính kiểu Integer (để khớp với bảng don_hang)
            $table->integer('id_khuyenmai')->autoIncrement(); 
            
            // Cột này em đánh dấu FK nhưng thực chất nó là mã code dạng chuỗi (ví dụ: TET2026)
            $table->string('Ma_voucher', 50)->unique(); 
            
            $table->string('Tenkhuyenmai', 255);
            $table->string('Loai_giamgia', 50);
            
            $table->bigInteger('gia_trigiam');
            $table->bigInteger('don_toithieu')->default(0); // Mặc định là 0 theo ERD
            $table->bigInteger('giam_toida')->nullable();
            
            $table->integer('soluongma');
            $table->integer('dasudung')->default(0);
            
            $table->timestamp('ngaybdchuongtrinh')->nullable();
            $table->timestamp('ngayketthucchuongtrinh')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khuyen_mai');
    }
};
