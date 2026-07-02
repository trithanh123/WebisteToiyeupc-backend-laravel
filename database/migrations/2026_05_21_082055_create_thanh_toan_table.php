<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thanh_toan', function (Blueprint $table) {
            $table->integer('id_thanhtoan')->autoIncrement();
            $table->unsignedBigInteger('ma_donhang');
            $table->integer('soluong');
            $table->string('phuong_thuc', 50);
            $table->string('ma_giaodich', 100)->nullable();
            $table->bigInteger('sotien');
            $table->string('trangthai', 50);
            $table->timestamp('ngaythanhtoan')->useCurrent();
            $table->timestamps();

            $table->foreign('ma_donhang')
                  ->references('id_donhang')
                  ->on('don_hang')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thanh_toan');
    }
};
