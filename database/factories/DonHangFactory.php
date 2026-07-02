<?php
namespace Database\Factories;
use App\Models\don_hang;
use App\Models\Nguoi_dung;
use App\Models\chi_nhanh;
use App\Models\diachi_nguoidung;
use Illuminate\Database\Eloquent\Factories\Factory;
class DonHangFactory extends Factory
{
    protected $model = don_hang::class;
    public function definition(): array
    {
        $nguoiDung = Nguoi_dung::factory()->create();
        return [
            'ma_nguoidung'       => $nguoiDung->id_nguoidung,
            'ma_chinhanh'        => chi_nhanh::factory(),
            'ma_khuyenmai'       => null,
            'ma_diachinguoidung' => diachi_nguoidung::factory()->choNguoiDung($nguoiDung->id_nguoidung),
            'tongtien'           => fake()->randomElement([15000000, 20000000, 35000000, 5000000]),
            'phuong_thuc_tt'     => fake()->randomElement(['Tiền mặt', 'Chuyển khoản', 'Thẻ tín dụng']),
            'trang_thai_dh'      => fake()->randomElement(['Chờ xác nhận', 'Đang giao hàng', 'Đã giao', 'Đã hủy']),
            'ghichu'             => fake()->optional(0.3)->sentence(), 
            'thoigiandathang'    => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
