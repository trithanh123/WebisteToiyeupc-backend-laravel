<?php
namespace Database\Factories;
use App\Models\nhan_vien;
use App\Models\Nguoi_dung;
use App\Models\chi_nhanh;
use Illuminate\Database\Eloquent\Factories\Factory;
class NhanVienFactory extends Factory
{
    protected $model = nhan_vien::class;
    private static array $chucVuList = [
        'Tư vấn bán hàng',
        'Kỹ thuật viên',
        'Quản lý kho',
        'Thu ngân',
        'Trưởng chi nhánh',
        'Nhân viên giao hàng',
        'Hỗ trợ kỹ thuật',
    ];
    public function definition(): array
    {
        return [
            'id_nguoidung' => Nguoi_dung::factory()->laNhanVien(), 
            'chucvu'       => fake()->randomElement(self::$chucVuList),
            'machinhanh'   => null, 
        ];
    }
    public function choNguoiDung(int $idNguoiDung): static
    {
        return $this->state(['id_nguoidung' => $idNguoiDung]);
    }
    public function taiBranch(int $idChiNhanh): static
    {
        return $this->state(['machinhanh' => $idChiNhanh]);
    }
    public function laTruongChiNhanh(): static
    {
        return $this->state(['chucvu' => 'Trưởng chi nhánh']);
    }
}
