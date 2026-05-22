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
        Schema::create('nguoi_dung', function (Blueprint $table) {
            // Khóa chính
            $table->id('id_NguoiDung');             
            // Các trường thông tin
            $table->string('Ten', 255);
            $table->string('email', 255)->unique();
            $table->string('matkhau', 255);
            $table->string('SDT', 20)->nullable(); // Đổi SĐT thành SDT để tránh lỗi font SQL
            $table->string('MaNCC', 50)->nullable();
            $table->string('MaNCC_id', 255)->nullable();
            // Phân quyền (Ví dụ: 0: Khách, 1: Admin, 2: Nhân viên, 3: Nhà cung cấp)
            $table->tinyInteger('Phanquyen')->default(0);
            $table->timestamp('email_verified_at')->nullable(); // Dùng cho chức năng quên mật khẩu của Laravel
            $table->rememberToken(); // Dùng cho chức năng "Ghi nhớ đăng nhập"
            // Tự động sinh ra 2 cột thời gian (tương đương thoigiantao và thoigiancapnhat)
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
