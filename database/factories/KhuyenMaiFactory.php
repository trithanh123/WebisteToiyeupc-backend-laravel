<?php
namespace Database\Factories;
use App\Models\khuyen_mai;
use Illuminate\Database\Eloquent\Factories\Factory;
class KhuyenMaiFactory extends Factory
{
    protected $model = khuyen_mai::class;
    public function definition(): array
    {
        $nam   = now()->year;
        $ma    = strtoupper(fake()->lexify('????')) . $nam;
        return [
            'ma_voucher'             => $ma,
            'tenkhuyenmai'           => 'Giảm giá ' . fake()->randomElement(['Tết', 'Hè', 'Khai trường', 'Black Friday', 'Giáng sinh']),
            'loai_giamgia'           => 'so_tien',      
            'gia_trigiam'            => fake()->randomElement([50000, 100000, 200000, 500000]),
            'don_toithieu'           => fake()->randomElement([500000, 1000000, 2000000, 5000000]),
            'giam_toida'             => null,
            'soluongma'              => fake()->numberBetween(50, 500),
            'dasudung'               => 0,
            'ngaybdchuongtrinh'      => now(),
            'ngayketthucchuongtrinh' => now()->addDays(fake()->numberBetween(7, 30)),
        ];
    }
    public function giamPhanTram(int $phantram = 10, int $giamToida = 500000): static
    {
        return $this->state([
            'loai_giamgia' => 'phan_tram',
            'gia_trigiam'  => $phantram,        
            'giam_toida'   => $giamToida,       
        ]);
    }
    public function theoDip(string $ten, string $maPrefix = 'SALE'): static
    {
        return $this->state(function () use ($ten, $maPrefix) {
            return [
                'ma_voucher'   => $maPrefix . now()->year . fake()->numerify('##'),
                'tenkhuyenmai' => $ten,
                'soluongma'    => fake()->numberBetween(100, 1000),
            ];
        });
    }
    public function vip(): static
    {
        return $this->state([
            'loai_giamgia' => 'so_tien',
            'gia_trigiam'  => fake()->randomElement([1000000, 2000000, 5000000]),
            'don_toithieu' => fake()->randomElement([10000000, 20000000, 30000000]),
            'soluongma'    => fake()->numberBetween(5, 20),
        ]);
    }
    public function daHetHan(): static
    {
        return $this->state([
            'ngaybdchuongtrinh'      => now()->subMonths(2),
            'ngayketthucchuongtrinh' => now()->subDays(1),
        ]);
    }
    public function daHetLuot(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'soluongma' => 100,
                'dasudung'  => 100,
            ];
        });
    }
}
