<?php
namespace Database\Factories;
use App\Models\ThongBao;
use Illuminate\Database\Eloquent\Factories\Factory;
class ThongBaoFactory extends Factory
{
    protected $model = ThongBao::class;
    public function definition(): array
    {
        return [
            'loai_thong_bao' => fake()->randomElement(['ORDER', 'SYSTEM', 'PROMOTION', 'WAREHOUSE']),
            'tieu_de'        => fake()->sentence(),
            'noi_dung'       => fake()->paragraph(),
            'nguoi_doc'      => '[]', 
            'link'           => fake()->optional(0.5)->url(),
        ];
    }
}
