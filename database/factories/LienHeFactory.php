<?php
namespace Database\Factories;
use App\Models\lien_he;
use App\Models\Nguoi_dung;
use Illuminate\Database\Eloquent\Factories\Factory;
class LienHeFactory extends Factory
{
    protected $model = lien_he::class;
    public function definition(): array
    {
        $hasNguoiDung = fake()->boolean(70); 
        return [
            'ma_nguoidung' => $hasNguoiDung ? Nguoi_dung::factory() : null,
            'ten_lienhe'   => fake('vi_VN')->name(),
            'email_lienhe' => fake()->safeEmail(),
            'sdt'          => '0' . fake()->numerify('9########'),
            'website'      => fake()->optional()->domainName(),
            'noidung'      => fake()->paragraph(),
            'trangthai'    => fake()->randomElement([0, 1, 2]), 
        ];
    }
}
