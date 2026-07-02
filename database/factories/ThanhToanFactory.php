<?php
namespace Database\Factories;
use App\Models\thanh_toan;
use App\Models\don_hang;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class ThanhToanFactory extends Factory
{
    protected $model = thanh_toan::class;
    public function definition(): array
    {
        $phuongThuc = fake()->randomElement(['Tiền mặt', 'Chuyển khoản', 'Thẻ tín dụng', 'MoMo', 'VNPAY']);
        return [
            'ma_donhang'    => don_hang::factory(),
            'soluong'       => fake()->numberBetween(1, 3), 
            'phuong_thuc'   => $phuongThuc,
            'ma_giaodich'   => ($phuongThuc !== 'Tiền mặt') ? strtoupper(Str::random(10)) : null,
            'sotien'        => fake()->randomElement([15000000, 30000000, 500000]),
            'trangthai'     => fake()->randomElement(['Chờ thanh toán', 'Đã thanh toán', 'Đã hoàn tiền', 'Thất bại']),
            'ngaythanhtoan' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
