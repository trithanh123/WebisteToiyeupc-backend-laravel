<?php
namespace Database\Factories;
use App\Models\danh_gia;
use App\Models\Nguoi_dung;
use App\Models\san_pham;
use Illuminate\Database\Eloquent\Factories\Factory;
class DanhGiaFactory extends Factory
{
    protected $model = danh_gia::class;
    public function definition(): array
    {
        return [
            'ma_nguoidung' => Nguoi_dung::factory(),
            'ma_sanpham'   => san_pham::factory(),
            'danhgia'      => fake()->numberBetween(1, 5),
            'binhluan'     => fake()->optional(0.8)->paragraph(), 
            'thoigiantao'  => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
