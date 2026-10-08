<?php

namespace Database\Seeders;

use App\Models\san_pham;
use App\Models\chi_nhanh;
use App\Models\danh_muc;
use App\Models\ton_kho_cuc_bo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Them150SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        $chiNhanhs = chi_nhanh::all();
        if ($chiNhanhs->isEmpty()) {
            $this->command->warn('Không có chi nhánh nào!');
            return;
        }

        // Lấy toàn bộ các danh mục lá (không có danh mục con)
        $categories = danh_muc::whereDoesntHave('danhMucCon')->get();
        $this->command->info("Bắt đầu tạo sản phẩm cho " . $categories->count() . " danh mục lá (đảm bảo chính xác thông số và giá)...");

        $bar = $this->command->getOutput()->createProgressBar($categories->count() * 3);
        $bar->start();

        $totalSp = 0;
        $totalTk = 0;

        foreach ($categories as $cat) {
            $nameStr = $cat->ten_danhmuc . ' ' . str_replace('-', ' ', $cat->slug);
            
            // Xác định loại sản phẩm
            $type = 'Sản phẩm';
            if (stripos($nameStr, 'pc') !== false) $type = 'PC';
            if (stripos($nameStr, 'laptop') !== false) $type = 'Laptop';
            if (stripos($nameStr, 'man hinh') !== false || stripos($nameStr, 'màn hình') !== false) $type = 'Màn hình';
            if (stripos($nameStr, 'chuot') !== false || stripos($nameStr, 'chuột') !== false) $type = 'Chuột';
            if (stripos($nameStr, 'ban phim') !== false || stripos($nameStr, 'bàn phím') !== false) $type = 'Bàn phím';
            
            $minPrice = 500000;
            $maxPrice = 2000000;

            // Phân tích giá chính xác từ tên danh mục
            if (preg_match('/dưới (\d+) triệu/i', $nameStr, $m) || preg_match('/duoi-(\d+)-trieu/i', $nameStr, $m)) {
                $maxPrice = $m[1] * 1000000;
                $minPrice = $maxPrice * 0.7;
            } elseif (preg_match('/từ (\d+) đến (\d+) triệu/i', $nameStr, $m) || preg_match('/tu-(\d+)-den-(\d+)-trieu/i', $nameStr, $m) || preg_match('/(\d+) triệu - (\d+) triệu/i', $nameStr, $m) || preg_match('/tu-(\d+)-(\d+)-trieu/i', $nameStr, $m)) {
                $minPrice = $m[1] * 1000000;
                $maxPrice = $m[2] * 1000000;
            } elseif (preg_match('/trên (\d+) triệu/i', $nameStr, $m) || preg_match('/tren-(\d+)-trieu/i', $nameStr, $m)) {
                $minPrice = $m[1] * 1000000;
                $maxPrice = $minPrice + 10000000;
            } elseif (preg_match('/dưới (\d+) nghìn/i', $nameStr, $m)) {
                $maxPrice = $m[1] * 1000;
                $minPrice = 100000;
            } elseif (preg_match('/từ (\d+) nghìn - (\d+) triệu/i', $nameStr, $m)) {
                $minPrice = $m[1] * 1000;
                $maxPrice = $m[2] * 1000000;
            } else {
                if ($type == 'PC' || $type == 'Laptop') {
                    $minPrice = 10000000; $maxPrice = 30000000;
                } elseif ($type == 'Màn hình') {
                    $minPrice = 2000000; $maxPrice = 10000000;
                }
            }

            $specs = [];
            
            // Đảm bảo thông số Card Đồ Họa (VGA) CHÍNH XÁC
            if (preg_match('/RTX (\d+)( Ti| SUPER)?/i', $nameStr, $m)) {
                $specs['VGA'] = 'NVIDIA GeForce RTX ' . $m[1] . ($m[2] ?? '');
            } elseif (preg_match('/GTX (\d+)/i', $nameStr, $m)) {
                $specs['VGA'] = 'NVIDIA GeForce GTX ' . $m[1];
            } elseif (preg_match('/RX (\d+)/i', $nameStr, $m)) {
                $specs['VGA'] = 'AMD Radeon RX ' . $m[1];
            }

            // Đảm bảo thông số CPU CHÍNH XÁC
            if (preg_match('/Core i(\d+)/i', $nameStr, $m) || preg_match('/ i(\d+)/i', $nameStr, $m)) {
                $specs['CPU'] = 'Intel Core i' . $m[1];
            } elseif (preg_match('/Ultra (\d+)/i', $nameStr, $m)) {
                $specs['CPU'] = 'Intel Core Ultra ' . $m[1];
            } elseif (preg_match('/Ryzen (\d+)/i', $nameStr, $m) || preg_match('/R(\d+)/i', $nameStr, $m)) {
                $specs['CPU'] = 'AMD Ryzen ' . $m[1];
            } elseif (preg_match('/Athlon/i', $nameStr)) {
                $specs['CPU'] = 'AMD Athlon';
            }

            // Đảm bảo thông số Hãng (Brand) CHÍNH XÁC
            $brands = ['ASUS', 'MSI', 'Gigabyte', 'Logitech', 'Razer', 'Corsair', 'Acer', 'Lenovo', 'Dell', 'LG', 'Samsung', 'ViewSonic', 'AOC', 'HKC', 'Philips', 'E-Dra', 'VSP', 'BenQ', 'Kingston', 'Western Digital', 'Seagate', 'Toshiba', 'PNY', 'Sandisk'];
            foreach ($brands as $b) {
                if (stripos($nameStr, $b) !== false) {
                    $specs['Hãng'] = $b;
                    break;
                }
            }

            // Các thông số khác
            if (preg_match('/(\d+)Hz/i', $nameStr, $m)) {
                $specs['Tần số quét'] = $m[0];
            }
            if (preg_match('/(\d+) inch/i', $nameStr, $m) || preg_match('/(\d+)"/i', $nameStr, $m)) {
                $specs['Kích thước'] = $m[1] . ' inch';
            }
            if (preg_match('/(\d+)GB/i', $nameStr, $m) || preg_match('/(\d+) GB/i', $nameStr, $m)) {
                $specs['Dung lượng'] = $m[1] . 'GB';
            }
            if (preg_match('/(\d+)TB/i', $nameStr, $m) || preg_match('/(\d+) TB/i', $nameStr, $m)) {
                $specs['Dung lượng'] = $m[1] . 'TB';
            }
            
            // Tạo 3 sản phẩm cho mỗi danh mục lá để trang nào cũng có sản phẩm
            for ($i = 1; $i <= 3; $i++) {
                $price = rand($minPrice, $maxPrice);
                $price = round($price / 10000) * 10000; // Làm tròn đến chục nghìn
                if ($price <= 0) $price = 500000;
                
                $brandStr = isset($specs['Hãng']) ? $specs['Hãng'] . ' ' : '';
                $productName = trim($type . ' ' . $brandStr . $cat->ten_danhmuc . ' Model ' . Str::upper(Str::random(5)));
                
                // Loại bỏ từ trùng lặp nếu có
                $productName = str_replace('Màn hình Màn hình', 'Màn hình', $productName);
                $productName = str_replace('PC PC', 'PC', $productName);
                $productName = str_replace('Laptop Laptop', 'Laptop', $productName);

                $mota = "Sản phẩm " . $productName . " cao cấp, phù hợp mọi nhu cầu. \n";
                foreach ($specs as $k => $v) {
                    $mota .= "- $k: $v\n";
                }

                $sp = san_pham::withoutEvents(function () use ($cat, $productName, $price, $mota, $specs) {
                    return san_pham::create([
                        'ma_danhmuc'     => $cat->id_danhmuc,
                        'masp'           => 'SP-' . Str::upper(Str::random(8)),
                        'tensp'          => $productName,
                        'gia'            => $price,
                        'thumbail'       => null,
                        'motasanpham'    => $mota,
                        'specifications' => $specs,
                    ]);
                });
                $totalSp++;

                // Thêm tồn kho cho tất cả chi nhánh
                foreach ($chiNhanhs as $cn) {
                    ton_kho_cuc_bo::withoutEvents(function () use ($sp, $cn) {
                        ton_kho_cuc_bo::create([
                            'ma_sanpham'     => $sp->id_sanpham,
                            'ma_chinhanh'    => $cn->id_chinhanh,
                            'soluongtonkho'  => rand(5, 50),
                            'soluongkhothap' => rand(3, 10),
                        ]);
                    });
                    $totalTk++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Hoàn tất! Đã tạo thành công {$totalSp} sản phẩm CHÍNH XÁC và {$totalTk} bản ghi tồn kho trên tất cả các trang danh mục.");
    }
}
