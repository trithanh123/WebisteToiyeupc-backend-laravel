<?php
namespace Database\Factories;
use App\Models\ton_kho_cuc_bo;
use App\Models\san_pham;
use App\Models\chi_nhanh;
use Illuminate\Database\Eloquent\Factories\Factory;
class TonKhoCucBoFactory extends Factory
{
    protected $model = ton_kho_cuc_bo::class;
    public function definition(): array
    {
        return [
            'ma_sanpham'     => san_pham::factory(),
            'ma_chinhanh'    => chi_nhanh::factory(),
            'soluongtonkho'  => fake()->numberBetween(0, 100),
            'soluongkhothap' => fake()->numberBetween(5, 10),
        ];
    }
}
