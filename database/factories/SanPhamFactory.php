<?php
namespace Database\Factories;
use App\Models\san_pham;
use App\Models\danh_muc;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SanPhamFactory extends Factory
{
    protected $model = san_pham::class;

    // ── Laptop thông thường ───────────────────────────────────────
    private static array $specsLaptop = [
        [
            'brand'       => 'ASUS',
            'cpu'         => 'Intel Core i5-12500H',
            'ram'         => '8GB DDR4',
            'storage'     => '512GB NVMe SSD',
            'gpu'         => 'Intel Iris Xe Graphics',
            'screen_size' => '15.6 inch FHD',
            'use_case'    => 'văn phòng, học tập, lập trình',
        ],
        [
            'brand'       => 'Lenovo',
            'cpu'         => 'Intel Core i7-1355U',
            'ram'         => '16GB DDR5',
            'storage'     => '512GB NVMe SSD',
            'gpu'         => 'Intel Iris Xe Graphics',
            'screen_size' => '14 inch FHD',
            'use_case'    => 'văn phòng, học tập, mỏng nhẹ',
        ],
        [
            'brand'       => 'Acer',
            'cpu'         => 'AMD Ryzen 5 7530U',
            'ram'         => '8GB DDR4',
            'storage'     => '256GB NVMe SSD',
            'gpu'         => 'AMD Radeon Graphics',
            'screen_size' => '15.6 inch FHD',
            'use_case'    => 'học tập, sinh viên, giá rẻ',
        ],
        [
            'brand'       => 'HP',
            'cpu'         => 'Intel Core i5-1335U',
            'ram'         => '8GB DDR4',
            'storage'     => '512GB NVMe SSD',
            'gpu'         => 'Intel Iris Xe Graphics',
            'screen_size' => '14 inch FHD',
            'use_case'    => 'văn phòng, doanh nhân, mỏng nhẹ',
        ],
        [
            'brand'       => 'Dell',
            'cpu'         => 'Intel Core i7-1360P',
            'ram'         => '16GB DDR5',
            'storage'     => '1TB NVMe SSD',
            'gpu'         => 'Intel Iris Xe Graphics',
            'screen_size' => '13.3 inch FHD+',
            'use_case'    => 'văn phòng, doanh nhân, di động',
        ],
    ];

    // ── Laptop Gaming ─────────────────────────────────────────────
    private static array $specsLaptopGaming = [
        [
            'brand'       => 'ASUS ROG',
            'cpu'         => 'Intel Core i9-13900H',
            'ram'         => '32GB DDR5',
            'storage'     => '1TB NVMe SSD',
            'gpu'         => 'NVIDIA GeForce RTX 4070 8GB',
            'screen_size' => '16 inch QHD 165Hz',
            'use_case'    => 'chơi game, streaming, đồ họa',
        ],
        [
            'brand'       => 'MSI',
            'cpu'         => 'AMD Ryzen 9 7945HX',
            'ram'         => '32GB DDR5',
            'storage'     => '2TB NVMe SSD',
            'gpu'         => 'NVIDIA GeForce RTX 4090 16GB',
            'screen_size' => '17.3 inch QHD 240Hz',
            'use_case'    => 'chơi game nặng, render, streaming',
        ],
        [
            'brand'       => 'Lenovo Legion',
            'cpu'         => 'Intel Core i7-13700H',
            'ram'         => '16GB DDR5',
            'storage'     => '1TB NVMe SSD',
            'gpu'         => 'NVIDIA GeForce RTX 4060 8GB',
            'screen_size' => '15.6 inch FHD 144Hz',
            'use_case'    => 'chơi game, esports, học sinh sinh viên',
        ],
        [
            'brand'       => 'Acer Nitro',
            'cpu'         => 'Intel Core i5-13500H',
            'ram'         => '16GB DDR5',
            'storage'     => '512GB NVMe SSD',
            'gpu'         => 'NVIDIA GeForce RTX 4050 6GB',
            'screen_size' => '15.6 inch FHD 144Hz',
            'use_case'    => 'chơi game nhập vai, game bắn súng giá rẻ',
        ],
    ];

    // ── PC bàn (giá gắn theo cấu hình thực tế GearVN 2025) ─────────────
    private static array $specsPC = [
        [   // PC homework văn phòng AMD R3 ~8-10tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'AMD Ryzen 3 4100',
            'ram'       => '8GB DDR4',
            'storage'   => '256GB NVMe SSD',
            'gpu'       => 'AMD Radeon Graphics (tích hợp)',
            'mainboard' => 'MSI A520M-A Pro',
            'psu'       => '450W 80+ Bronze',
            'case'      => 'Xigmatek Asgard Pro',
            'use_case'  => 'văn phòng, học tập, lướt web',
            'gia'       => 8_990_000,
        ],
        [   // PC homework AMD R5 ~10-13tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'AMD Ryzen 5 5600G',
            'ram'       => '8GB DDR4',
            'storage'   => '256GB NVMe SSD',
            'gpu'       => 'AMD Radeon Graphics RX Vega 7 (tích hợp)',
            'mainboard' => 'Gigabyte A520M DS3H',
            'psu'       => '500W 80+ Bronze',
            'case'      => 'Xigmatek Asgard Pro',
            'use_case'  => 'văn phòng, học tập, xem phim',
            'gia'       => 10_390_000,
        ],
        [   // PC i5 + RTX 3050 ~18tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i5-12400F',
            'ram'       => '16GB DDR4',
            'storage'   => '500GB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 3050 8GB',
            'mainboard' => 'MSI B760M Pro-E DDR4',
            'psu'       => '650W 80+ Bronze',
            'case'      => 'Lian Li Lancool 216',
            'use_case'  => 'chơi game tầm trung, văn phòng',
            'gia'       => 17_990_000,
        ],
        [   // PC i5 + RTX 3060 ~21tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i5-12400F',
            'ram'       => '16GB DDR4',
            'storage'   => '500GB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 3060 12GB',
            'mainboard' => 'MSI B760M Pro-E DDR4',
            'psu'       => '650W 80+ Bronze',
            'case'      => 'Lian Li Lancool 216',
            'use_case'  => 'chơi game 1080p, esports, streaming',
            'gia'       => 21_490_000,
        ],
        [   // PC i5 + RTX 5060 Ti ~26tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i5-12400F',
            'ram'       => '16GB DDR4',
            'storage'   => '500GB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5060 Ti 8GB',
            'mainboard' => 'MSI B760M Pro-E DDR4',
            'psu'       => '650W 80+ Gold',
            'case'      => 'Corsair 4000D Airflow',
            'use_case'  => 'chơi game 1080p-1440p, streaming',
            'gia'       => 25_590_000,
        ],
        [   // PC i5-14400F + RTX 5060 DDR5 ~31tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i5-14400F',
            'ram'       => '16GB DDR5',
            'storage'   => '500GB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5060 8GB',
            'mainboard' => 'ASUS Prime B760M-K D4',
            'psu'       => '700W 80+ Gold',
            'case'      => 'Corsair 4000D Airflow',
            'use_case'  => 'chơi game 1440p, esports',
            'gia'       => 30_990_000,
        ],
        [   // PC i7-14700F + RTX 5060 ~38tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i7-14700F',
            'ram'       => '32GB DDR5',
            'storage'   => '1TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5060 8GB',
            'mainboard' => 'ASUS Prime Z790-P WiFi',
            'psu'       => '750W 80+ Gold',
            'case'      => 'NZXT H7 Flow',
            'use_case'  => 'chơi game 1440p, render, streaming',
            'gia'       => 37_890_000,
        ],
        [   // PC i7 + RTX 5070 ~53tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i7-14700F',
            'ram'       => '32GB DDR5',
            'storage'   => '1TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5070 12GB',
            'mainboard' => 'ASUS Prime Z790-P WiFi',
            'psu'       => '850W 80+ Gold',
            'case'      => 'NZXT H7 Flow',
            'use_case'  => 'chơi game 4K, render 3D, thiết kế đồ họa',
            'gia'       => 52_690_000,
        ],
        [   // PC i7 + RTX 5080 ~77tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i7-14700K',
            'ram'       => '32GB DDR5',
            'storage'   => '2TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5080 16GB',
            'mainboard' => 'ASUS ROG STRIX Z790-F Gaming WiFi',
            'psu'       => '1000W 80+ Platinum',
            'case'      => 'Phanteks Enthoo Pro II',
            'use_case'  => 'chơi game 4K, AI, render phim chuyên nghiệp',
            'gia'       => 76_890_000,
        ],
        [   // PC i9 + RTX 5090 Workstation ~220tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i9-14900K',
            'ram'       => '64GB DDR5',
            'storage'   => '2TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5090 24GB',
            'mainboard' => 'ASUS ROG MAXIMUS Z790 APEX',
            'psu'       => '1200W 80+ Titanium',
            'case'      => 'Lian Li O11 Dynamic EVO XL',
            'use_case'  => 'workstation AI/ML, render phim 8K, game 4K ultra',
            'gia'       => 221_990_000,
        ],
        [   // PC AMD R5 + RX 7800 XT ~22tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'AMD Ryzen 5 7600X',
            'ram'       => '16GB DDR5',
            'storage'   => '500GB NVMe SSD',
            'gpu'       => 'AMD RX 7800 XT 16GB',
            'mainboard' => 'MSI B650M Mortar WiFi',
            'psu'       => '700W 80+ Gold',
            'case'      => 'DeepCool CH510',
            'use_case'  => 'chơi game 1440p, đồ họa, giá tốt',
            'gia'       => 22_500_000,
        ],
        [   // PC AMD R7 + RTX 5070 Ti ~65tr
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'AMD Ryzen 7 7700X',
            'ram'       => '32GB DDR5',
            'storage'   => '1TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 5070 Ti 16GB',
            'mainboard' => 'Gigabyte B650 AORUS Elite AX',
            'psu'       => '850W 80+ Gold',
            'case'      => 'NZXT H7 Flow',
            'use_case'  => 'chơi game 4K, thiết kế đồ họa chuyên nghiệp',
            'gia'       => 65_000_000,
        ],
        [   // PC i9 + RTX 4080 Super ~125tr (Phân khúc 100-200tr)
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i9-14900K',
            'ram'       => '64GB DDR5 6000MHz',
            'storage'   => '2TB NVMe SSD Gen4',
            'gpu'       => 'NVIDIA GeForce RTX 4080 SUPER 16GB',
            'mainboard' => 'ASUS ROG MAXIMUS Z790 HERO',
            'psu'       => '1000W 80+ Platinum',
            'case'      => 'Corsair 5000D AIRFLOW',
            'use_case'  => 'chơi game 4K max setting, render 3D nặng, streaming',
            'gia'       => 125_990_000,
        ],
        [   // PC Ryzen 9 7950X3D + RTX 4090 ~185tr (Phân khúc 100-200tr)
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'AMD Ryzen 9 7950X3D',
            'ram'       => '64GB DDR5 6000MHz',
            'storage'   => '4TB NVMe SSD Gen4',
            'gpu'       => 'NVIDIA GeForce RTX 4090 24GB',
            'mainboard' => 'ASUS ROG CROSSHAIR X670E HERO',
            'psu'       => '1200W 80+ Platinum',
            'case'      => 'Lian Li O11 Dynamic EVO',
            'use_case'  => 'workstation đồ họa cao cấp, training AI, gaming 8K',
            'gia'       => 185_500_000,
        ],
        [   // PC i7 + RTX 4070 Ti Super ~85tr (Phân khúc 70-100tr)
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i7-14700K',
            'ram'       => '32GB DDR5 6000MHz',
            'storage'   => '2TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 4070 Ti SUPER 16GB',
            'mainboard' => 'MSI MAG Z790 TOMAHAWK WIFI',
            'psu'       => '850W 80+ Gold',
            'case'      => 'NZXT H9 Flow',
            'use_case'  => 'chơi game 4K, đồ họa chuyên nghiệp, edit video',
            'gia'       => 85_990_000,
        ],
        [   // PC i5-13400F + RTX 4060 ~23tr (Phân khúc dưới 30tr)
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'Intel Core i5-13400F',
            'ram'       => '16GB DDR4 3200MHz',
            'storage'   => '500GB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 4060 8GB',
            'mainboard' => 'ASUS TUF GAMING B760M-PLUS',
            'psu'       => '650W 80+ Bronze',
            'case'      => 'Montech AIR 100 ARGB',
            'use_case'  => 'chơi game AAA 1080p, học tập, văn phòng',
            'gia'       => 23_490_000,
        ],
        [   // PC Ryzen 5 7600 + RTX 4070 SUPER ~42tr (Phân khúc 30-50tr)
            'brand'     => 'ToiYeuPC',
            'cpu'       => 'AMD Ryzen 5 7600',
            'ram'       => '32GB DDR5 5600MHz',
            'storage'   => '1TB NVMe SSD',
            'gpu'       => 'NVIDIA GeForce RTX 4070 SUPER 12GB',
            'mainboard' => 'Gigabyte B650M AORUS ELITE',
            'psu'       => '750W 80+ Gold',
            'case'      => 'DeepCool CH560',
            'use_case'  => 'chơi game 2K mượt mà, đồ họa 3D',
            'gia'       => 42_990_000,
        ],
    ];


    // ── Màn hình ─────────────────────────────────────────────────
    private static array $specsManHinh = [
        ['brand' => 'LG',        'screen_size' => '24 inch', 'use_case' => 'văn phòng, giải trí',              'refresh_rate' => '75Hz',  'panel' => 'IPS'],
        ['brand' => 'Dell',      'screen_size' => '27 inch', 'use_case' => 'đồ họa, thiết kế, văn phòng',      'refresh_rate' => '60Hz',  'panel' => 'IPS'],
        ['brand' => 'ASUS',      'screen_size' => '27 inch', 'use_case' => 'chơi game, esports',               'refresh_rate' => '144Hz', 'panel' => 'IPS'],
        ['brand' => 'Samsung',   'screen_size' => '32 inch', 'use_case' => 'giải trí, xem phim, văn phòng',   'refresh_rate' => '60Hz',  'panel' => 'VA'],
        ['brand' => 'ViewSonic', 'screen_size' => '34 inch', 'use_case' => 'đa nhiệm, lập trình, thiết kế',   'refresh_rate' => '100Hz', 'panel' => 'IPS'],
        ['brand' => 'AOC',       'screen_size' => '24 inch', 'use_case' => 'chơi game FPS, esports',           'refresh_rate' => '240Hz', 'panel' => 'IPS'],
    ];

    // ── Linh kiện ────────────────────────────────────────────────
    private static array $specsLinhKien = [
        ['brand' => 'Kingston', 'loai' => 'RAM', 'ram'     => '16GB DDR4 3200MHz',  'use_case' => 'nâng cấp RAM máy tính bàn'],
        ['brand' => 'Corsair',  'loai' => 'RAM', 'ram'     => '32GB DDR5 5600MHz',  'use_case' => 'nâng cấp RAM hiệu năng cao'],
        ['brand' => 'Samsung',  'loai' => 'SSD', 'storage' => '1TB NVMe M.2',       'use_case' => 'nâng cấp ổ cứng tốc độ cao'],
        ['brand' => 'WD',       'loai' => 'SSD', 'storage' => '2TB NVMe M.2',       'use_case' => 'lưu trữ dung lượng lớn, game, video'],
        ['brand' => 'Seagate',  'loai' => 'HDD', 'storage' => '2TB HDD 7200rpm',    'use_case' => 'lưu trữ dữ liệu, backup'],
        ['brand' => 'G.Skill',  'loai' => 'RAM', 'ram'     => '16GB DDR5 6000MHz',  'use_case' => 'overclock, gaming hiệu năng'],
    ];

    // ── Phụ kiện ─────────────────────────────────────────────────
    private static array $specsPhuKien = [
        ['brand' => 'Logitech', 'loai' => 'Chuột Gaming',    'use_case' => 'chơi game FPS, MOBA'],
        ['brand' => 'Razer',    'loai' => 'Chuột Gaming',    'use_case' => 'chơi game chuyên nghiệp, esports'],
        ['brand' => 'Akko',     'loai' => 'Bàn phím cơ',     'use_case' => 'gõ phím, lập trình, gaming'],
        ['brand' => 'Corsair',  'loai' => 'Bàn phím cơ',     'use_case' => 'gaming, streaming'],
        ['brand' => 'Dare-U',   'loai' => 'Chuột Văn phòng', 'use_case' => 'văn phòng, học tập, giá rẻ'],
        ['brand' => 'ASUS',     'loai' => 'Bàn phím giả cơ', 'use_case' => 'gaming giá rẻ, sinh viên'],
    ];

    // ── Definition mặc định: Laptop thường ───────────────────────
    public function definition(): array
    {
        $specs = fake()->randomElement(self::$specsLaptop);
        $hang  = $specs['brand'];
        $tensp = 'Laptop ' . $hang . ' ' . strtoupper(fake()->lexify('???')) . fake()->numerify('##');
        $gia   = fake()->randomElement([12990000, 15990000, 18990000, 22990000, 28990000]);

        return [
            'ma_danhmuc'     => null,
            'masp'           => strtoupper(fake()->lexify('??')) . '-' . fake()->unique()->numerify('####'),
            'tensp'          => $tensp,
            'gia'            => $gia,
            'thumbail'       => 'products/' . Str::slug($tensp) . '.jpg',
            'motasanpham'    => "Laptop {$hang} chính hãng, trang bị {$specs['cpu']}, RAM {$specs['ram']}, "
                              . "ổ cứng {$specs['storage']}, màn hình {$specs['screen_size']}. "
                              . "Phù hợp cho {$specs['use_case']}.",
            'specifications' => $specs,
        ];
    }

    // ── PC bàn ───────────────────────────────────────────────────
    public function laPC(): static
    {
        return $this->state(function () {
            $specs = fake()->randomElement(self::$specsPC);
            $gia   = $specs['gia'] ?? 18_000_000; // lấy giá từ specs, không random
            unset($specs['gia']); // không lưu 'gia' vào specifications JSON

            // Chọn ảnh theo GPU
            $gpu = $specs['gpu'] ?? '';
            $thumbnail = match(true) {
                str_contains($gpu, 'RTX 5090') => 'products/pc_gaming_rtx5090.jpg',
                str_contains($gpu, 'RTX 5080') => 'products/pc_gaming_rtx5080.jpg',
                str_contains($gpu, 'RTX 5070 Ti') => 'products/pc_gaming_rtx5070.jpg',
                str_contains($gpu, 'RTX 5070') => 'products/pc_gaming_rtx5070.jpg',
                str_contains($gpu, 'RTX 5060 Ti') => 'products/pc_gaming_rtx5060ti.jpg',
                str_contains($gpu, 'RTX 5060') => 'products/pc_gaming_rtx5060.jpg',
                str_contains($gpu, 'RTX 3060') => 'products/pc_gaming_rtx3060.jpg',
                str_contains($gpu, 'RTX 3050') => 'products/pc_gaming_rtx3050.jpg',
                str_contains($gpu, 'RX 7800') => 'products/pc_gaming_rx7800xt.jpg',
                str_contains($gpu, 'tích hợp') => 'products/pc_gaming_homework.jpg',
                default => 'products/pc_gaming_rtx3060.jpg',
            };

            $tensp = 'PC ToiYeuPC Gaming ' . strtoupper(fake()->lexify('????'));
            return [
                'tensp'          => $tensp,
                'gia'            => $gia,
                'thumbail'       => $thumbnail,
                'motasanpham'    => "PC Gaming lắp ráp chính hãng ToiYeuPC, cấu hình {$specs['cpu']}, "
                                  . "card đồ họa {$specs['gpu']}, RAM {$specs['ram']}, nguồn {$specs['psu']}. "
                                  . "Phù hợp cho {$specs['use_case']}.",
                'specifications' => $specs,
            ];
        });
    }

    // ── Laptop Gaming ─────────────────────────────────────────────
    public function laLaptopGaming(): static
    {
        return $this->state(function () {
            $specs = fake()->randomElement(self::$specsLaptopGaming);
            $hang  = $specs['brand'];
            $tensp = "Laptop Gaming {$hang} " . strtoupper(fake()->lexify('????'));
            return [
                'tensp'          => $tensp,
                'gia'            => fake()->randomElement([29990000, 35990000, 45990000, 55990000, 79990000]),
                'motasanpham'    => "Laptop Gaming {$hang} hiệu năng cao, {$specs['cpu']}, {$specs['gpu']}, "
                                  . "RAM {$specs['ram']}, màn hình {$specs['screen_size']}. "
                                  . "Chinh phục mọi tựa game với {$specs['use_case']}.",
                'specifications' => $specs,
            ];
        });
    }

    // ── Màn hình ─────────────────────────────────────────────────
    public function laManHinh(): static
    {
        return $this->state(function () {
            $specs = fake()->randomElement(self::$specsManHinh);
            $hang  = $specs['brand'];
            $tensp = "Màn hình {$hang} {$specs['screen_size']} {$specs['refresh_rate']}";
            return [
                'tensp'          => $tensp,
                'gia'            => fake()->randomElement([2500000, 3990000, 5500000, 8900000, 15000000]),
                'motasanpham'    => "Màn hình {$hang} {$specs['screen_size']}, tần số quét {$specs['refresh_rate']}, "
                                  . "tấm nền {$specs['panel']}. Phù hợp cho {$specs['use_case']}.",
                'specifications' => $specs,
            ];
        });
    }

    // ── Linh kiện ────────────────────────────────────────────────
    public function laLinhKien(): static
    {
        return $this->state(function () {
            $specs = fake()->randomElement(self::$specsLinhKien);
            $hang  = $specs['brand'];
            $loai  = $specs['loai'];
            $ten   = isset($specs['ram'])
                ? "{$loai} {$specs['ram']} {$hang}"
                : "{$loai} {$specs['storage']} {$hang}";
            return [
                'tensp'          => $ten,
                'gia'            => fake()->randomElement([850000, 1250000, 2100000, 3500000, 4800000]),
                'motasanpham'    => "Linh kiện {$loai} chính hãng {$hang}, chất lượng cao, "
                                  . "bảo hành chính hãng. Phù hợp cho {$specs['use_case']}.",
                'specifications' => $specs,
            ];
        });
    }

    // ── Phụ kiện ─────────────────────────────────────────────────
    public function laPhuKien(): static
    {
        return $this->state(function () {
            $specs = fake()->randomElement(self::$specsPhuKien);
            $hang  = $specs['brand'];
            $loai  = $specs['loai'];
            return [
                'tensp'          => "{$loai} {$hang} " . strtoupper(fake()->lexify('???')),
                'gia'            => fake()->randomElement([550000, 1290000, 2590000, 4500000]),
                'motasanpham'    => "{$loai} {$hang} chất lượng cao, thiết kế công thái học. "
                                  . "Phù hợp cho {$specs['use_case']}.",
                'specifications' => $specs,
            ];
        });
    }

    // ── Helpers ──────────────────────────────────────────────────
    public function thuocDanhMuc(int $idDanhMuc): static
    {
        return $this->state(['ma_danhmuc' => $idDanhMuc]);
    }

    public function giaRe(): static
    {
        return $this->state([
            'gia' => fake()->randomElement([7990000, 9990000, 11990000, 13990000]),
        ]);
    }
}

