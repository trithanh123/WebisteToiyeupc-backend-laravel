<?php
namespace Database\Factories;
use App\Models\diachi_nguoidung;
use App\Models\Nguoi_dung;
use Illuminate\Database\Eloquent\Factories\Factory;
class DiaChiNguoiDungFactory extends Factory
{
    protected $model = diachi_nguoidung::class;
    private static array $quanHuyen = [
        ['ma_quan' => 760, 'ma_thanhpho' => 79, 'ten' => 'Quận 1'],
        ['ma_quan' => 761, 'ma_thanhpho' => 79, 'ten' => 'Quận 3'],
        ['ma_quan' => 762, 'ma_thanhpho' => 79, 'ten' => 'Quận 5'],
        ['ma_quan' => 763, 'ma_thanhpho' => 79, 'ten' => 'Quận 10'],
        ['ma_quan' => 764, 'ma_thanhpho' => 79, 'ten' => 'Bình Thạnh'],
        ['ma_quan' => 765, 'ma_thanhpho' => 79, 'ten' => 'Gò Vấp'],
        ['ma_quan' => 769, 'ma_thanhpho' => 79, 'ten' => 'Thủ Đức'],
        ['ma_quan' => 770, 'ma_thanhpho' => 79, 'ten' => 'Tân Bình'],
    ];
    public function definition(): array
    {
        $khu = fake()->randomElement(self::$quanHuyen);
        return [
            'id_nguoidung'   => Nguoi_dung::factory(), 
            'ten_nguoinhan'  => fake('vi_VN')->name(),
            'sdt_nguoinhan'  => '0' . fake()->numerify('9########'),
            'ma_thanhpho'    => $khu['ma_thanhpho'],
            'ma_quan'        => $khu['ma_quan'],
            'ma_phuong'      => fake()->numberBetween(1, 15),
            'diachi_chitiet' => fake()->buildingNumber() . ' đường ' . fake()->streetName() . ', ' . $khu['ten'],
            'matudien_diachi' => false,
        ];
    }
    public function laMacDinh(): static
    {
        return $this->state(['matudien_diachi' => true]);
    }
    public function choNguoiDung(int $idNguoiDung): static
    {
        return $this->state(['id_nguoidung' => $idNguoiDung]);
    }
}
