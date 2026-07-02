<?php
namespace Database\Factories;
use App\Models\chi_tiet_don_hang;
use App\Models\don_hang;
use App\Models\san_pham;
use Illuminate\Database\Eloquent\Factories\Factory;
class ChiTietDonHangFactory extends Factory
{
    protected $model = chi_tiet_don_hang::class;
    public function definition(): array
    {
        $soLuong = fake()->numberBetween(1, 5);
        $donGia = fake()->randomElement([15000000, 2000000, 500000, 30000000]);
        return [
            'ma_donhang' => don_hang::factory(),
            'ma_sanpham' => san_pham::factory(),
            'soluong'    => $soLuong,
            'don_gia'    => $donGia,
            'thanh_tien' => $soLuong * $donGia,
        ];
    }
}
