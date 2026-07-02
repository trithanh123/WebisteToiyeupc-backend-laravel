<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc', function (Blueprint $table) {
            $table->id('id_danhmuc');
            $table->string('ten_danhmuc', 255);
            $table->string('slug', 255)->unique();
            $table->unsignedBigInteger('danhmuc_cha')->nullable();
            $table->string('hinhanh_icon', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc');
    }
};