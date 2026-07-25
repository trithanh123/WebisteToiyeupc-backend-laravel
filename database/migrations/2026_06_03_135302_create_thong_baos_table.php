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
            $table->string('loai_thong_bao', 50); // VD: 'don_hang_moi', 'thanh_toan_that_bai'
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->json('nguoi_doc')->default('[]'); // Danh sách id người đã đọc: [1, 2, 3]
            $table->string('link')->nullable(); // Điều hướng đến trang liên quan
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('thong_bao');
    }
};
