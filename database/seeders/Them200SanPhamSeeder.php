<?php

namespace Database\Seeders;

use App\Models\san_pham;
use App\Models\chi_nhanh;
use App\Models\ton_kho_cuc_bo;
use Illuminate\Database\Seeder;

class Them200SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        $chiNhanhs = chi_nhanh::all();

        if ($chiNhanhs->isEmpty()) {
            $this->command->warn('Không có chi nhánh nào để thêm tồn kho!');
            return;
        }

        $this->command->info('Bắt đầu tạo 200 PC Gaming theo chuẩn giá GearVN...');
        
        $bar = $this->command->getOutput()->createProgressBar(200);
        $bar->start();

        $totalTonKho = 0;

        san_pham::withoutEvents(function () use ($chiNhanhs, $bar, &$totalTonKho) {
            for ($i = 0; $i < 200; $i++) {
                // Tạo ra 1 sản phẩm PC từ factory (các spec đã được random và gán giá thực tế)
                $sp = san_pham::factory()->laPC()->make();
                
                // Dựa vào giá để phân loại vào đúng Category ID
                $gia = $sp->gia;
                $maDanhMuc = 86; // Mặc định: PC dưới 30 triệu (ID 86)
                
                if ($gia >= 30_000_000 && $gia < 50_000_000) {
                    $maDanhMuc = 87; // PC từ 30 - 50 triệu
                } elseif ($gia >= 50_000_000 && $gia < 70_000_000) {
                    $maDanhMuc = 88; // PC từ 50 - 70 triệu
                } elseif ($gia >= 70_000_000 && $gia < 100_000_000) {
                    $maDanhMuc = 89; // PC từ 70 - 100 triệu
                } elseif ($gia >= 100_000_000 && $gia < 200_000_000) {
                    $maDanhMuc = 90; // PC từ 100 - 200 triệu
                } elseif ($gia >= 200_000_000) {
                    $maDanhMuc = 91; // PC trên 200 triệu
                }

                $sp->ma_danhmuc = $maDanhMuc;
                
                // Xử lý tensp để đảm bảo không trùng lặp
                $sp->masp = strtoupper(fake()->lexify('??')) . '-' . fake()->unique()->numerify('####');
                
                $sp->save();

                // Tạo tồn kho cho tất cả chi nhánh
                foreach ($chiNhanhs as $cn) {
                    ton_kho_cuc_bo::create([
                        'ma_sanpham'     => $sp->id_sanpham,
                        'ma_chinhanh'    => $cn->id_chinhanh,
                        'soluongtonkho'  => rand(5, 50),
                        'soluongkhothap' => rand(2, 5),
                    ]);
                    $totalTonKho++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info('Hoàn tất tạo 200 PC Gaming!');
        $this->command->info("Đã tạo {$totalTonKho} bản ghi tồn kho.");
    }
}
