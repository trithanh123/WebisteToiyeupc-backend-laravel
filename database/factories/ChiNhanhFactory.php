<?php
namespace Database\Factories;
use App\Models\chi_nhanh;
use Illuminate\Database\Eloquent\Factories\Factory;
class ChiNhanhFactory extends Factory
{
    protected $model = chi_nhanh::class;
    public function definition(): array
    {   
        $khuVuc = fake()->unique()->randomElement([
        'Quận 1', 'Quận 3', 'Quận 5', 'Quận 10', 'Bình Thạnh', 'Gò Vấp', 'Thủ Đức'
    ]);
        return [
            'ten_chinhanh'   => 'ToiYeuPC ' . $khuVuc,
        'ma_chinhanh'    => 'TYPC_' . fake()->unique()->numerify('###'), 
        'sdt_chinhanh'   => fake()->numerify('028 38## ####'), 
        'email_chinhanh' => 'chinhanh.' . fake()->word() . '@ToiYeuPC.vn',
        'diachi_chitiet' => fake()->buildingNumber() . ' ' . fake()->streetName() . ', ' . $khuVuc,
        'maso_phuong'    => fake()->numberBetween(1, 15),
        'maso_tp'        => 79, 
        'maso_tinh'      => 79, 
        'map_link'       => 'https://maps.google.com/?q=' . fake()->latitude() . ',' . fake()->longitude(),
        ];
    }
}
