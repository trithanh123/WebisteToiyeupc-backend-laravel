<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_he', function (Blueprint $table) {
            $table->id('id_lienhe');
            $table->unsignedBigInteger('ma_nguoidung')->nullable();
            $table->string('ten_lienhe', 255);
            $table->string('email_lienhe', 255);
            $table->string('sdt', 20);
            $table->string('website', 255)->nullable();
            $table->text('noidung');
            $table->tinyInteger('trangthai')->default(0);
            $table->timestamps();

            $table->foreign('ma_nguoidung')
                  ->references('id_nguoidung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_he');
    }
};
