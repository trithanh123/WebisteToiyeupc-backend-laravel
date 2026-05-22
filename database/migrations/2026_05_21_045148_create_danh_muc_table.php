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
        Schema::create('danh_muc', function (Blueprint $table) {
            $table->id('ID_DanhMuc'); // Khóa chính
            
            $table->string('Ten_DanhMuc', 255);
            $table->string('slug', 255)->unique();
            
            // Chỉ tạo cột để lưu ID danh mục cha, KHÔNG tạo ràng buộc khóa ngoại
            $table->unsignedBigInteger('DanhMuc_cha')->nullable();
            
            $table->string('Hinhanh_icon', 255)->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_muc');
    }
};