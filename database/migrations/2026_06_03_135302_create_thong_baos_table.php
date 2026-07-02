<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thong_bao', function (Blueprint $table) {
            $table->id('id_thongbao');
            $table->string('loai_thong_bao', 50); 
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->boolean('da_doc')->default(false); 
            $table->string('link')->nullable(); 
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('thong_bao');
    }
};
