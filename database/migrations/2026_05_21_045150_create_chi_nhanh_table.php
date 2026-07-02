<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_nhanh', function (Blueprint $table) {
    $table->id('id_chinhanh'); 
    $table->string('ten_chinhanh', 255);
    $table->string('ma_chinhanh', 50);
    $table->string('sdt_chinhanh', 20)->nullable(); 
    $table->string('email_chinhanh', 255)->nullable();
    $table->string('diachi_chitiet', 255);
    $table->integer('maso_phuong')->nullable();
    $table->integer('maso_tp')->nullable();
    $table->integer('maso_tinh')->nullable();
    $table->string('map_link', 500)->nullable();
    $table->timestamps();
});
    }
    public function down(): void
    {
        Schema::dropIfExists('chi_nhanh');
    }
};