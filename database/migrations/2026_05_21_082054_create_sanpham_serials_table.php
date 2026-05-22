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
        Schema::create('sanpham_serials', function (Blueprint $table) {
            $table->id('ID_Serial'); // Khóa chính Bigint
            
            // Cột khóa ngoại trỏ về Tồn kho cục bộ
            $table->unsignedBigInteger('MaTonKho');
            
            $table->string('serial_code', 100)->unique();
            
            $table->enum('tinhtrang', [
                'nằm trong kho', 
                'đã bán', 
                'đang bảo hành', 
                'trong quá trình đổi trả/luân chuyển ', 
                'đã mất'
            ]);
            
            $table->integer('Min_Soluongkho')->default(0); 
            
            $table->timestamp('Ngaycuthe')->useCurrent();
            
            $table->timestamps();

            // Ràng buộc khóa ngoại CHÍNH THỨC (Vì bảng Ton_kho_cuc_bo đã tồn tại)
            $table->foreign('MaTonKho')
                  ->references('ID_Khoton')
                  ->on('ton_kho_cuc_bo')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanpham_serials');
    }
};
