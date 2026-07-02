<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanpham_serials', function (Blueprint $table) {
            $table->id('id_serial');
            $table->unsignedBigInteger('ma_tonkho');
            $table->string('serial_code', 100)->unique();
            $table->enum('tinhtrang', [
                'nằm trong kho',
                'đã bán',
                'đang bảo hành',
                'trong quá trình đổi trả/luân chuyển',
                'đã mất',
            ]);
            $table->integer('min_soluongkho')->default(0);
            $table->timestamp('ngaycuthe')->useCurrent();
            $table->timestamps();

            $table->foreign('ma_tonkho')
                  ->references('id_khoton')
                  ->on('ton_kho_cuc_bo')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanpham_serials');
    }
};
