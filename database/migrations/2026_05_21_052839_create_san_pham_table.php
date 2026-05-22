<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kích hoạt extension vector của PostgreSQL
        //DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        // 2. Tạo bảng Sản Phẩm
        Schema::create('san_pham', function (Blueprint $table) {
            $table->id('ID_SanPham'); // Khóa chính
            
            // Cột khóa ngoại (phải cùng kiểu unsignedBigInteger với ID_DanhMuc)
            $table->unsignedBigInteger('Ma_DanhMuc');
            
            $table->string('MaSP', 100)->unique();
            $table->string('TenSP', 255);
            $table->bigInteger('Gia');
            $table->string('Thumbail', 255)->nullable();
            $table->text('Motasanpham')->nullable();
            
            // Trường JSONB lưu thông số kỹ thuật động
            $table->jsonb('specifications')->nullable();
            
            $table->timestamps();

            // Ràng buộc khóa ngoại trỏ về bảng danh_muc
            $table->foreign('Ma_DanhMuc')
                  ->references('ID_DanhMuc')
                  ->on('danh_muc')
                  ->onDelete('cascade');
        });

        // 3. Thêm cột lưu Vector 1536 chiều đúng chuẩn ERD
       // DB::statement('ALTER TABLE san_pham ADD COLUMN embedding vector(1536);');
    }

    public function down(): void
    {
        Schema::dropIfExists('san_pham');
    }
};