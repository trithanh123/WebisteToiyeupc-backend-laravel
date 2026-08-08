<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\san_pham;
use App\Models\chi_nhanh;
use App\Models\ton_kho_cuc_bo;
use Illuminate\Support\Str;

class LinhKienBuilderSeeder extends Seeder
{
    public function run(): void
    {
        $chiNhanhs = chi_nhanh::all();
        if ($chiNhanhs->isEmpty()) return;

        $this->command->info('Bắt đầu tạo Linh Kiện để phục vụ tính năng AI PC Builder...');

        $components = [
            // CPU
            ['ma_danhmuc' => 165, 'loai' => 'CPU', 'tensp' => 'CPU Intel Core i3-12100F', 'gia' => 2000000, 'brand' => 'Intel', 'socket' => 'LGA1700', 'use_case' => 'văn phòng, lập trình cơ bản, chơi game nhẹ'],
            ['ma_danhmuc' => 165, 'loai' => 'CPU', 'tensp' => 'CPU Intel Core i5-12400F', 'gia' => 3500000, 'brand' => 'Intel', 'socket' => 'LGA1700', 'use_case' => 'chơi game, lập trình, đồ họa 2D'],
            ['ma_danhmuc' => 165, 'loai' => 'CPU', 'tensp' => 'CPU Intel Core i7-13700K', 'gia' => 10500000, 'brand' => 'Intel', 'socket' => 'LGA1700', 'use_case' => 'chơi game nặng, đồ họa 3D, lập trình di động'],
            ['ma_danhmuc' => 165, 'loai' => 'CPU', 'tensp' => 'CPU Intel Core i9-14900K', 'gia' => 15000000, 'brand' => 'Intel', 'socket' => 'LGA1700', 'use_case' => 'chơi game 4K, AI, render phim'],
            ['ma_danhmuc' => 171, 'loai' => 'CPU', 'tensp' => 'CPU AMD Ryzen 5 5600X', 'gia' => 3800000, 'brand' => 'AMD', 'socket' => 'AM4', 'use_case' => 'chơi game, lập trình, render video'],
            ['ma_danhmuc' => 171, 'loai' => 'CPU', 'tensp' => 'CPU AMD Ryzen 7 7800X3D', 'gia' => 11000000, 'brand' => 'AMD', 'socket' => 'AM5', 'use_case' => 'chơi game siêu mượt, lập trình, 3D'],

            // Mainboard
            ['ma_danhmuc' => 151, 'loai' => 'Mainboard', 'tensp' => 'Mainboard ASUS PRIME H610M-K', 'gia' => 1800000, 'brand' => 'ASUS', 'socket' => 'LGA1700', 'use_case' => 'văn phòng, cơ bản'],
            ['ma_danhmuc' => 151, 'loai' => 'Mainboard', 'tensp' => 'Mainboard MSI PRO B760M-E', 'gia' => 2800000, 'brand' => 'MSI', 'socket' => 'LGA1700', 'use_case' => 'chơi game, lập trình, ổn định'],
            ['ma_danhmuc' => 151, 'loai' => 'Mainboard', 'tensp' => 'Mainboard GIGABYTE Z790 AORUS ELITE', 'gia' => 7000000, 'brand' => 'Gigabyte', 'socket' => 'LGA1700', 'use_case' => 'chơi game cao cấp, ép xung, render'],
            ['ma_danhmuc' => 157, 'loai' => 'Mainboard', 'tensp' => 'Mainboard ASUS TUF GAMING B550M-PLUS', 'gia' => 3200000, 'brand' => 'ASUS', 'socket' => 'AM4', 'use_case' => 'chơi game, lập trình'],
            ['ma_danhmuc' => 157, 'loai' => 'Mainboard', 'tensp' => 'Mainboard MSI MAG B650 TOMAHAWK WIFI', 'gia' => 5500000, 'brand' => 'MSI', 'socket' => 'AM5', 'use_case' => 'chơi game, lập trình, đa nhiệm'],

            // VGA
            ['ma_danhmuc' => 148, 'loai' => 'VGA', 'tensp' => 'VGA GIGABYTE GeForce RTX 3060 12GB', 'gia' => 7500000, 'brand' => 'Gigabyte', 'power' => '500W', 'use_case' => 'chơi game PUBG, đồ họa 2D, lập trình'],
            ['ma_danhmuc' => 148, 'loai' => 'VGA', 'tensp' => 'VGA ASUS DUAL GeForce RTX 4060 8GB', 'gia' => 8500000, 'brand' => 'ASUS', 'power' => '550W', 'use_case' => 'chơi game mượt, đồ họa, lập trình'],
            ['ma_danhmuc' => 148, 'loai' => 'VGA', 'tensp' => 'VGA MSI GeForce RTX 4070 Ti SUPER 16GB', 'gia' => 24000000, 'brand' => 'MSI', 'power' => '750W', 'use_case' => 'chơi game 4K, đồ họa 3D, lập trình Android, AI'],
            ['ma_danhmuc' => 148, 'loai' => 'VGA', 'tensp' => 'VGA ASUS ROG Strix GeForce RTX 4090 24GB', 'gia' => 60000000, 'brand' => 'ASUS', 'power' => '1000W', 'use_case' => 'chơi game đỉnh cao, AI, render siêu nặng'],
            ['ma_danhmuc' => 148, 'loai' => 'VGA', 'tensp' => 'VGA ASUS Phoenix GeForce GTX 1650 4GB', 'gia' => 3500000, 'brand' => 'ASUS', 'power' => '300W', 'use_case' => 'văn phòng, game nhẹ LOL, lập trình'],

            // RAM
            ['ma_danhmuc' => 218, 'loai' => 'RAM', 'tensp' => 'RAM Kingston FURY Beast 8GB DDR4 3200MHz', 'gia' => 600000, 'brand' => 'Kingston', 'use_case' => 'văn phòng, cơ bản'],
            ['ma_danhmuc' => 218, 'loai' => 'RAM', 'tensp' => 'RAM Corsair Vengeance LPX 16GB (2x8GB) DDR4 3200MHz', 'gia' => 1200000, 'brand' => 'Corsair', 'use_case' => 'chơi game, lập trình, đa nhiệm'],
            ['ma_danhmuc' => 219, 'loai' => 'RAM', 'tensp' => 'RAM G.Skill Trident Z5 RGB 32GB (2x16GB) DDR5 6000MHz', 'gia' => 3500000, 'brand' => 'G.Skill', 'use_case' => 'chơi game nặng, render, lập trình Visual Studio, Android Studio'],
            
            // SSD
            ['ma_danhmuc' => 234, 'loai' => 'SSD', 'tensp' => 'SSD Kingston NV2 250GB PCIe Gen4 x4 NVMe', 'gia' => 700000, 'brand' => 'Kingston', 'use_case' => 'văn phòng, cơ bản'],
            ['ma_danhmuc' => 234, 'loai' => 'SSD', 'tensp' => 'SSD Western Digital Blue SN580 500GB', 'gia' => 1200000, 'brand' => 'Western Digital', 'use_case' => 'chơi game, lập trình'],
            ['ma_danhmuc' => 234, 'loai' => 'SSD', 'tensp' => 'SSD Samsung 990 PRO 1TB PCIe Gen4 NVMe', 'gia' => 2800000, 'brand' => 'Samsung', 'use_case' => 'chơi game nặng, render, lưu trữ lớn'],
            
            // PSU
            ['ma_danhmuc' => 194, 'loai' => 'PSU', 'tensp' => 'Nguồn DeepCool PF450 450W', 'gia' => 800000, 'brand' => 'DeepCool', 'use_case' => 'văn phòng, cơ bản'],
            ['ma_danhmuc' => 194, 'loai' => 'PSU', 'tensp' => 'Nguồn Corsair CV650 650W 80 Plus Bronze', 'gia' => 1600000, 'brand' => 'Corsair', 'use_case' => 'chơi game, lập trình'],
            ['ma_danhmuc' => 194, 'loai' => 'PSU', 'tensp' => 'Nguồn ASUS TUF Gaming 750W 80 Plus Gold', 'gia' => 2800000, 'brand' => 'ASUS', 'use_case' => 'chơi game nặng, đồ họa'],
            ['ma_danhmuc' => 194, 'loai' => 'PSU', 'tensp' => 'Nguồn Corsair RM1000e 1000W 80 Plus Gold', 'gia' => 4500000, 'brand' => 'Corsair', 'use_case' => 'chơi game đỉnh cao, render'],
            
            // Case
            ['ma_danhmuc' => 178, 'loai' => 'Case', 'tensp' => 'Case Xigmatek NYX 3F', 'gia' => 600000, 'brand' => 'Xigmatek', 'use_case' => 'văn phòng, cơ bản'],
            ['ma_danhmuc' => 178, 'loai' => 'Case', 'tensp' => 'Case Corsair 4000D Airflow Tempered Glass', 'gia' => 2200000, 'brand' => 'Corsair', 'use_case' => 'chơi game, lập trình, tản nhiệt tốt'],
            ['ma_danhmuc' => 178, 'loai' => 'Case', 'tensp' => 'Case Lian Li O11 Dynamic EVO', 'gia' => 4000000, 'brand' => 'Lian Li', 'use_case' => 'chơi game nặng, trưng bày, cao cấp'],
        ];

        san_pham::withoutEvents(function () use ($components, $chiNhanhs) {
            foreach ($components as $comp) {
                // Prepare specifications
                $specs = [
                    'brand'    => $comp['brand'],
                    'loai'     => $comp['loai'],
                    'use_case' => $comp['use_case']
                ];
                if (isset($comp['socket'])) $specs['socket'] = $comp['socket'];
                if (isset($comp['power']))  $specs['power']  = $comp['power'];

                $sp = new san_pham();
                $sp->ma_danhmuc     = $comp['ma_danhmuc'];
                $sp->masp           = strtoupper(fake()->lexify('??')) . '-' . fake()->unique()->numerify('####');
                $sp->tensp          = $comp['tensp'];
                $sp->gia            = $comp['gia'];
                $sp->thumbail       = 'products/component_placeholder.jpg';
                $sp->motasanpham    = "Linh kiện {$comp['loai']} - {$comp['tensp']}. Phù hợp cho: {$comp['use_case']}.";
                $sp->specifications = $specs;
                $sp->save();

                foreach ($chiNhanhs as $cn) {
                    ton_kho_cuc_bo::create([
                        'ma_sanpham'     => $sp->id_sanpham,
                        'ma_chinhanh'    => $cn->id_chinhanh,
                        'soluongtonkho'  => rand(10, 50),
                        'soluongkhothap' => 5,
                    ]);
                }
            }
        });

        $this->command->info("Đã tạo xong " . count($components) . " linh kiện phục vụ Build PC!");
    }
}
