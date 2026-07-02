<?php
namespace Database\Factories;
use App\Models\danh_muc;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class DanhMucFactory extends Factory
{
    protected $model = danh_muc::class;
    public static array $cauTruc = [
        'Laptop' => [
            'Thương hiệu'          => ['ASUS', 'ACER', 'MSI', 'LENOVO', 'LG - Gram'],
            'Giá bán'              => ['Dưới 15 triệu', 'Từ 15 đến 20 triệu', 'Trên 20 triệu'],
            'CPU Intel - AMD'      => ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'AMD Ryzen'],
            'Nhu cầu sử dụng'      => ['Đồ họa - Studio', 'Học sinh - Sinh viên', 'Mỏng nhẹ cao cấp'],
            'Linh phụ kiện Laptop' => ['Ram laptop', 'SSD laptop', 'Ổ cứng di động'],
            'Laptop ASUS'          => ['ASUS OLED Series', 'Vivobook Series', 'Zenbook Series'],
            'Laptop ACER'          => ['Aspire Series', 'Swift Series'],
            'Laptop MSI'           => ['Modern Series', 'Prestige Series'],
            'Laptop Lenovo'        => ['Thinkbook Series', 'Ideapad Series', 'Thinkpad Series', 'Yoga Series'],
        ],
        'Laptop Gaming' => [
            'Thương hiệu Gaming'       => ['ACER / PREDATOR', 'ASUS / ROG', 'MSI', 'LENOVO', 'GIGABYTE / AORUS'],
            'Giá bán Gaming'            => ['Dưới 20 triệu', 'Từ 20 đến 25 triệu', 'Từ 25 đến 30 triệu', 'Trên 30 triệu'],
            'ACER | PREDATOR'           => ['Nitro ProPanel Series', 'Nitro Series', 'Aspire Series', 'Predator Series'],
            'ASUS | ROG Gaming'         => ['ROG Series', 'TUF Series', 'Zephyrus Series'],
            'MSI Gaming'                => ['Titan GT Series', 'Stealth GS Series', 'Raider GE Series', 'Vector GP Series', 'Crosshair / Pulse GL Series', 'Sword / Katana GF66 Series', 'Cyborg / Thin GF Series', 'MSI RTX 50 Series'],
            'LENOVO Gaming'             => ['Legion Series', 'LOQ Series'],
            'GIGABYTE Gaming'           => ['Gaming Gigabyte', 'GIGABYTE RTX 50 Series'],
            'Cấu hình Gaming'           => ['RTX 50 Series', 'CPU Core Ultra', 'CPU AMD'],
            'Linh - Phụ kiện Laptop'    => ['Ram laptop', 'SSD laptop', 'Ổ cứng di động'],
        ],
        'PC ToiYeuPC' => [
            'PC theo giá'              => ['PC dưới 30 triệu', 'PC từ 30 - 50 triệu', 'PC từ 50 - 70 triệu', 'PC từ 70 - 100 triệu', 'PC từ 100 - 200 triệu', 'PC trên 200 triệu'],
            'Quà khủngce 365  - Đón hè'       => ['PC Core i7 tặng màn', 'PC Ultra 7 tặng màn', 'PC Ryzen 7 tặng màn', 'PC tặng MÀN OLED', 'PC i5 tặng Màn (HOT)'],
            'PC theo CPU Intel'         => ['PC Core i3', 'PC Core i5 (HOT)', 'PC Core i7 (Tặng Màn)', 'PC Core i9'],
            'PC theo CPU Intel (Ultra)' => ['PC Ultra 5', 'PC Ultra 7', 'PC Ultra 9'],
            'PC theo CPU AMD'           => ['PC AMD R3', 'PC AMD R5 (HOT)', 'PC AMD R7', 'PC AMD R9'],
            'PC theo cấu hình VGA'     => ['PC RTX 5090', 'PC RTX 5080', 'PC RTX 5070Ti', 'PC RTX 5070', 'PC RTX 5060Ti'],
            'PC theo VGA phổ thông'     => ['PC RTX 5060 (HOT)', 'PC RTX 5050', 'PC RTX 3060', 'PC RTX 3050'],
            'PC Văn phòng'              => ['PC i5 - Tặng Màn 3tr (HOT)', 'Homework Athlon', 'Homework AMD R3', 'Homework AMD R5', 'Homework Intel i5'],
        ],
        'Main, CPU, VGA' => [
            'VGA RTX 50 Series'     => ['RTX 5090', 'RTX 5080', 'RTX 5070Ti', 'RTX 5070', 'RTX 5060Ti', 'RTX 5060'],
            'VGA Trên 12GB VRAM'    => ['RTX 4070 SUPER (12GB)', 'RTX 4070Ti SUPER (16GB)', 'RTX 4080 SUPER (16GB)'],
            'VGA Dưới 12GB VRAM'    => ['RTX 4060Ti (8 - 16GB)', 'RTX 4060 (8GB)', 'RTX 3060 (12GB)', 'RTX 3050 (6 - 8GB)', 'GTX 1650 (4GB)', 'GT 710 / GT 1030 (2-4GB)'],
            'VGA - Card màn hình'   => ['NVIDIA Quadro', 'AMD Radeon'],
            'Bo mạch chủ Intel'     => ['Z890 (Mới)', 'Z790', 'B760', 'H610', 'X299X'],
            'Bo mạch chủ AMD'       => ['AMD X870 (Mới)', 'AMD X670', 'AMD X570', 'AMD B650 (Mới)', 'AMD B550', 'AMD A320', 'AMD TRX40'],
            'CPU - Bộ vi xử lý Intel' => ['CPU Intel Core Ultra Series 2 (Mới)', 'CPU Intel 9', 'CPU Intel 7', 'CPU Intel 5', 'CPU Intel 3'],
            'CPU - Bộ vi xử lý AMD'   => ['CPU AMD Athlon', 'CPU AMD R3', 'CPU AMD R5', 'CPU AMD R7', 'CPU AMD R9'],
        ],
        'Case, Nguồn, Tản' => [
            'Case - Theo hãng'         => ['Case ASUS', 'Case Corsair', 'Case Lianli', 'Case NZXT', 'Case Jonsbo'],
            'Case - Theo giá'          => ['Dưới 1 triệu', 'Từ 1 triệu đến 2 triệu', 'Trên 2 triệu'],
            'Nguồn - Theo Hãng'        => ['Nguồn ASUS', 'Nguồn DeepCool', 'Nguồn Corsair', 'Nguồn NZXT', 'Nguồn MSI'],
            'Nguồn - Theo công suất'   => ['Từ 400w - 500w', 'Từ 500w - 600w', 'Từ 700w - 800w', 'Trên 1000w'],
            'Phụ kiện PC'              => ['Dây LED', 'Dây rise - Dựng VGA', 'Giá đỡ VGA', 'Keo tản nhiệt'],
            'Loại tản nhiệt'           => ['Tản nhiệt AIO 240mm', 'Tản nhiệt AIO 280mm', 'Tản nhiệt AIO 360mm', 'Tản nhiệt AIO 420mm', 'Tản nhiệt khí', 'Fan RGB'],
        ],
        'Ổ cứng, RAM, Thẻ nhớ' => [
            'Dung lượng RAM'    => ['8 GB', '16 GB', '32 GB', '64 GB'],
            'Loại RAM'          => ['DDR4', 'DDR5'],
            'Hãng RAM'          => ['Corsair', 'Kingston', 'G.Skill', 'PNY'],
            'Dung lượng HDD'    => ['HDD 1 TB', 'HDD 2 TB', 'HDD 4 TB - 6 TB', 'HDD trên 8 TB'],
            'Hãng HDD'          => ['Western Digital', 'Seagate', 'Toshiba'],
            'Dung lượng SSD'    => ['120GB - 128GB', '250GB - 256GB', '480GB - 512GB', '960GB - 1TB', '2TB', 'Trên 2TB'],
            'Hãng SSD'          => ['Samsung', 'Western Digital', 'Kingston', 'Corsair', 'PNY'],
            'Thẻ nhớ / USB'     => ['Sandisk', 'Kingston', 'Samsung'],
            'Ổ cứng di động'    => ['1TB', '2TB', '4TB', '5TB'],
        ],
        'Màn hình' => [
            'Hãng sản xuất'         => ['LG', 'Asus', 'ViewSonic', 'Dell', 'Gigabyte', 'AOC', 'Acer', 'HKC', 'MSI', 'Samsung', 'Philips', 'E-Dra', 'VSP', 'BenQ'],
            'Giá tiền'              => ['Dưới 5 triệu', 'Từ 5 triệu đến 10 triệu', 'Từ 10 triệu đến 20 triệu', 'Từ 20 triệu đến 30 triệu', 'Trên 30 triệu'],
            'Độ phân giải'          => ['Màn hình Full HD', 'Màn hình 2K 1440p', 'Màn hình 4K UHD', 'Màn hình 6K'],
            'Tần số quét'           => ['60Hz', '75Hz', '100Hz', '144Hz', '240Hz'],
            'Màn hình cong'         => ['24" Curved', '27" Curved', '32" Curved', 'Trên 32" Curved'],
            'Kích thước'            => ['Màn hình 22"', 'Màn hình 24"', 'Màn hình 27"', 'Màn hình 29"', 'Màn hình 32"', 'Màn hình Trên 32"', 'Hỗ trợ giá treo (VESA)'],
            'Màn hình đồ họa'      => ['Màn hình đồ họa 24"', 'Màn hình đồ họa 27"', 'Màn hình đồ họa 32"'],
            'Phụ kiện màn hình'     => ['Giá treo màn hình', 'Phụ kiện dây HDMI, DP, LAN'],
            'Màn hình di động'      => ['Full HD 1080p', '2K 1440p', 'Có cảm ứng'],
        ],
        'Bàn phím' => [
            'Thương hiệu'       => ['AKKO', 'AULA', 'Dare-U', 'Durgod', 'leobog', 'Keychron', 'FL-Esports', 'Corsair', 'E-Dra', 'Cidoo', 'Machenike', 'ASUS', 'Logitech', 'Razer', 'Leopold', 'Steelseries', 'Rapoo', 'VGN', 'MadLions', 'SKYLOONG'],
            'Giá Tiền'          => ['dưới 1 triệu', '1 triệu - 2 triệu', '2 triệu - 3 triệu', '3 triệu - 4 triệu', 'Trên 4 triệu'],
            'Kết Nối'           => ['Buletoolth', 'Wireless'],
            'Phụ Kiện Bàn Phím' => ['Keycap', 'Dwarf Factory', 'Kê Tay'],
        ],
        'Chuột' => [
            'Thương Hiệu Chuột'   => ['Logitech', 'Razer', 'Corsair', 'Microsoft', 'Dare U', 'ASUS', 'Steelseries', 'Glorious', 'Rapoo', 'Hyperx', 'ATK'],
            'Chuột Theo Giá Tiền' => ['Dưới 500 nghìn', 'Từ 500 nghìn - 1 triệu', 'Từ 1 triệu - 2 triệu', 'Trên 2 triệu - 3 triệu', 'Trên 3 triệu'],
            'Loại Chuột'          => ['Chuột chơi game', 'Chuột Văn Phòng'],
            'Logitech'            => ['Logitech Gaming', 'Logitech Văn Phòng'],
        ],
    ];
    public function definition(): array
    {
        $ten  = fake()->randomElement(array_keys(self::$cauTruc));
        $slug = Str::slug($ten) . '-' . fake()->unique()->numerify('##');
        return [
            'ten_danhmuc'  => $ten,
            'slug'         => $slug,
            'danhmuc_cha'  => null,   
            'hinhanh_icon' => null,
            'is_active'    => true,
        ];
    }
    public function laCon(int $idCha, string $tenCon = null): static
    {
        return $this->state(function () use ($idCha, $tenCon) {
            $ten  = $tenCon ?? fake()->words(2, true);
            return [
                'ten_danhmuc' => $ten,
                'slug'        => Str::slug($ten) . '-' . fake()->unique()->numerify('##'),
                'danhmuc_cha' => $idCha,
            ];
        });
    }
    public function laChau(int $idCon, string $tenChau = null): static
    {
        return $this->state(function () use ($idCon, $tenChau) {
            $ten  = $tenChau ?? fake()->words(3, true);
            return [
                'ten_danhmuc' => $ten,
                'slug'        => Str::slug($ten) . '-' . fake()->unique()->numerify('##'),
                'danhmuc_cha' => $idCon,
            ];
        });
    }
    public static function taoFullTree(): void
    {
        foreach (self::$cauTruc as $tenCha => $danhSachCon) {
            $slugCha = Str::slug($tenCha);
            $cha = danh_muc::create([
                'ten_danhmuc'  => $tenCha,
                'slug'         => $slugCha,
                'danhmuc_cha'  => null,
                'is_active'    => true,
            ]);
            foreach ($danhSachCon as $tenCon => $danhSachChau) {
                $slugCon = $slugCha . '-' . Str::slug($tenCon);
                $con = danh_muc::create([
                    'ten_danhmuc'  => $tenCon,
                    'slug'         => $slugCon,
                    'danhmuc_cha'  => $cha->id_danhmuc,
                    'is_active'    => true,
                ]);
                foreach ($danhSachChau as $tenChau) {
                    $slugChau = $slugCon . '-' . Str::slug($tenChau);
                    danh_muc::create([
                        'ten_danhmuc'  => $tenChau,
                        'slug'         => $slugChau,
                        'danhmuc_cha'  => $con->id_danhmuc,
                        'is_active'    => true,
                    ]);
                }
            }
        }
    }
}
