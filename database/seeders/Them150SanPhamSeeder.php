<?php

namespace Database\Seeders;

use App\Models\san_pham;
use App\Models\chi_nhanh;
use App\Models\danh_muc;
use App\Models\ton_kho_cuc_bo;
use Illuminate\Database\Seeder;

class Them150SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        
        $chiNhanhs = chi_nhanh::all();

        if ($chiNhanhs->isEmpty()) {
            $this->command->warn('  Không có chi nhánh nào!');
            return;
        }
        $this->command->info(" Tìm thấy {$chiNhanhs->count()} chi nhánh.");

    
        $dmPC       = danh_muc::where('slug', 'like', '%pc%')
                        ->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmLaptop   = danh_muc::where('slug', 'like', '%laptop%')
                        ->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmManHinh  = danh_muc::where('slug', 'like', '%man-hinh%')
                        ->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmLinhKien = danh_muc::where('slug', 'like', '%o-cung%')
                        ->orWhere('slug', 'like', '%ram%')
                        ->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');
        $dmPhuKien  = danh_muc::where('slug', 'like', '%chuot%')
                        ->orWhere('slug', 'like', '%ban-phim%')
                        ->whereDoesntHave('danhMucCon')->pluck('id_danhmuc');

      
        $all = collect();

      
        if ($dmPC->isNotEmpty()) {
            $this->command->info('🖥️  Tạo 150 PC Gaming...');
            $all = $all->merge(
                san_pham::factory(150)->laPC()
                    ->state(fn() => ['ma_danhmuc' => $dmPC->random()])
                    ->create()
            );
        }
        if ($dmLaptop->isNotEmpty()) {
            $this->command->info('💻 Tạo 40 Laptop thường...');
            $all = $all->merge(
                san_pham::factory(40)
                    ->state(fn() => ['ma_danhmuc' => $dmLaptop->random()])
                    ->create()
            );
        }
        if ($dmLaptop->isNotEmpty()) {
            $this->command->info('🎮 Tạo 30 Laptop Gaming...');
            $all = $all->merge(
                san_pham::factory(30)->laLaptopGaming()
                    ->state(fn() => ['ma_danhmuc' => $dmLaptop->random()])
                    ->create()
            );
        }
        if ($dmManHinh->isNotEmpty()) {
            $this->command->info('🖥️  Tạo 10 Màn hình...');
            $all = $all->merge(
                san_pham::factory(10)->laManHinh()
                    ->state(fn() => ['ma_danhmuc' => $dmManHinh->random()])
                    ->create()
            );
        }
        if ($dmLinhKien->isNotEmpty()) {
            $this->command->info('🔧 Tạo 5 Linh kiện...');
            $all = $all->merge(
                san_pham::factory(5)->laLinhKien()
                    ->state(fn() => ['ma_danhmuc' => $dmLinhKien->random()])
                    ->create()
            );
        }
        if ($dmPhuKien->isNotEmpty()) {
            $this->command->info('🖱️  Tạo 5 Phụ kiện...');
            $all = $all->merge(
                san_pham::factory(5)->laPhuKien()
                    ->state(fn() => ['ma_danhmuc' => $dmPhuKien->random()])
                    ->create()
            );
        }

        $this->command->info(" Đã tạo {$all->count()} sản phẩm.");

        $this->command->info(' Đang tạo tồn kho...');
        $bar = $this->command->getOutput()->createProgressBar($all->count());
        $bar->start();

        $total = 0;
        foreach ($all as $sp) {
            foreach ($chiNhanhs as $cn) {
                ton_kho_cuc_bo::create([
                    'ma_sanpham'     => $sp->id_sanpham,
                    'ma_chinhanh'    => $cn->id_chinhanh,
                    'soluongtonkho'  => rand(5, 100),
                    'soluongkhothap' => rand(3, 10),
                ]);
                $total++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info(' Hoàn tất!');
        $this->command->table(
            ['Loại', 'Số lượng'],
            [
                ['PC Gaming',     '150'],
                ['Laptop',        ' 40'],
                ['Laptop Gaming', ' 30'],
                ['Màn hình',      ' 10'],
                ['Linh kiện',     '  5'],
                ['Phụ kiện',      '  5'],
                ['─────────────', '────'],
                ['Tổng SP',       $all->count()],
                ['Bản ghi tồn kho', $total . " ({$all->count()} × {$chiNhanhs->count()})"],
            ]
        );
    }
}
