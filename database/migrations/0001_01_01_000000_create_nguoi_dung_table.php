<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id('id_nguoidung');             
            $table->string('ten', 255);
            $table->string('email', 255)->unique()->nullable();
            $table->string('matkhau', 255);
            $table->string('sdt', 20)->unique()->nullable(); 
            $table->date('ngaysinh')->nullable();
            $table->string('gioitinh', 10)->nullable();
            $table->string('mancc', 50)->nullable();
            $table->string('mancc_id', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->tinyInteger('phanquyen')->default(0);
            $table->timestamp('email_verified_at')->nullable(); 
            $table->rememberToken(); 
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
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
