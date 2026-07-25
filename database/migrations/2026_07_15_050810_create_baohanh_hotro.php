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
        Schema::create('baohanh_hotro', function (Blueprint $table) {
            $table->id();

            // Liên kết đơn hàng và khách hàng
            $table->unsignedBigInteger('ma_donhang')->nullable()->comment('Mã đơn hàng liên quan');
            $table->unsignedBigInteger('ma_nguoidung')->comment('Khách hàng yêu cầu');
            $table->unsignedBigInteger('ma_nhanvien')->nullable()->comment('Nhân viên tiếp nhận');
            $table->unsignedBigInteger('ma_chinhanh')->comment('Chi nhánh xử lý');

            // Liên kết serial number sản phẩm
            $table->unsignedBigInteger('ma_serial')->nullable()->comment('Serial sản phẩm bảo hành');

            // Thông tin yêu cầu
            $table->string('loai_yeu_cau')->default('Bảo hành')
                  ->comment('Bảo hành / Hỗ trợ kỹ thuật / Đổi trả');
            $table->text('mo_ta_loi')->comment('Mô tả lỗi của khách');

            // Trạng thái xử lý
            $table->string('trang_thai')->default('Chờ tiếp nhận')
                  ->comment('Chờ tiếp nhận / Đang xử lý / Hoàn thành / Từ chối');
            $table->text('ket_qua_xu_ly')->nullable()->comment('Ghi chú kết quả xử lý');

            // Thời gian
            $table->timestamp('ngay_tiep_nhan')->useCurrent();
            $table->timestamp('ngay_hoan_thanh')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('ma_donhang')->references('id_donhang')->on('don_hang')->nullOnDelete();
            $table->foreign('ma_nguoidung')->references('id_nguoidung')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('ma_nhanvien')->references('id_nhanvien')->on('nhanvien')->nullOnDelete();
            $table->foreign('ma_chinhanh')->references('id_chinhanh')->on('chi_nhanh')->cascadeOnDelete();
            $table->foreign('ma_serial')->references('id_serial')->on('sanpham_serials')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baohanh_hotro');
    }
};
