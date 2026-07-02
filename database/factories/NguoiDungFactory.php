<?php
namespace Database\Factories;
use App\Models\Nguoi_dung;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class NguoiDungFactory extends Factory
{
    protected $model = Nguoi_dung::class;
    public function definition(): array
    {
        return [
            'ten'      => fake('vi_VN')->name(),
            'email'    => fake()->unique()->safeEmail(),
            'matkhau'  => Hash::make('password123'),
            'sdt'      => '0' . fake()->numerify('9########'),
            'ngaysinh' => fake()->date('Y-m-d', '2005-01-01'),
            'gioitinh' => fake()->randomElement(['nam', 'nu', 'khac']),
            'mancc'    => null,
            'mancc_id' => null,
            'avatar'   => null,
            'phanquyen' => 0,  
            'email_verified_at' => now(),
        ];
    }
    public function laBoss(): static
    {
        return $this->state([
            'ten'       => 'Admin ToiYeuPC',
            'email'     => 'admin@toiyeupc.com',
            'phanquyen' => 1,
        ]);
    }
    public function laNhanVien(): static
    {
        return $this->state([
            'phanquyen' => 2,
        ]);
    }
    public function laNhaCungCap(): static
    {
        return $this->state(function () {
            $maNcc = 'NCC' . fake()->unique()->numerify('###');
            return [
                'phanquyen' => 3,
                'mancc'     => $maNcc,
                'mancc_id'  => Str::uuid()->toString(),
            ];
        });
    }
    public function chuaXacMinhEmail(): static
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }
}
