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
       Schema::create('thanh_toan', function (Blueprint $table) {
            // Khóa chính là INT theo đúng ERD
            $table->integer('id_ThanhToan')->autoIncrement(); 
            
            // Khóa ngoại trỏ về Đơn Hàng (phải là unsignedBigInteger)
            $table->unsignedBigInteger('MaDonHang');
            
            // Các thông tin giao dịch
            $table->integer('Soluong');
            $table->string('Phuong_thuc', 50);
            $table->string('Ma_giaodich', 100)->nullable(); // Cho phép null phòng trường hợp chưa thanh toán xong
            $table->bigInteger('SoTien');
            $table->string('trangthai', 50);
            
            $table->timestamp('ngaythanhtoan')->useCurrent();
            
            $table->timestamps();

            // Ràng buộc khóa ngoại
            $table->foreign('MaDonHang')
                  ->references('id_DonHang')
                  ->on('don_hang')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_toan');
    }
};
