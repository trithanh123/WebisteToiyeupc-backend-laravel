<?php
namespace Database\Factories;
use App\Models\sanpham_serials;
use App\Models\ton_kho_cuc_bo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class SanPhamSerialFactory extends Factory
{
    protected $model = sanpham_serials::class;
    public function definition(): array
    {
        return [
            'ma_tonkho'      => ton_kho_cuc_bo::factory(),
            'serial_code'    => strtoupper(Str::random(4)) . '-' . fake()->unique()->numerify('######'),
            'tinhtrang'      => fake()->randomElement([
                'nằm trong kho',
                'đã bán',
                'đang bảo hành',
                'trong quá trình đổi trả/luân chuyển',
                'đã mất',
            ]),
            'min_soluongkho' => 0,
            'ngaycuthe'      => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
