<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khuyen_mai', function (Blueprint $table) {
            $table->integer('id_khuyenmai')->autoIncrement();
            $table->string('ma_voucher', 50)->unique();
            $table->string('tenkhuyenmai', 255);
            $table->string('loai_giamgia', 50);
            $table->bigInteger('gia_trigiam');
            $table->bigInteger('don_toithieu')->default(0);
            $table->bigInteger('giam_toida')->nullable();
            $table->integer('soluongma');
            $table->integer('dasudung')->default(0);
            $table->timestamp('ngaybdchuongtrinh')->nullable();
            $table->timestamp('ngayketthucchuongtrinh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khuyen_mai');
    }
};
