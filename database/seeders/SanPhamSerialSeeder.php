<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ton_kho_cuc_bo;
use App\Models\sanpham_serials;
use Illuminate\Support\Facades\DB;

class SanPhamSerialSeeder extends Seeder
{
    public function run(): void
    {
        
        $khoThieu = ton_kho_cuc_bo::where('soluongtonkho', '>', 0)
            ->whereDoesntHave('serials', function ($q) {
                $q->where('tinhtrang', 'nằm trong kho');
            })
            ->with('sanPham')
            ->get();

        $this->command->info("Tìm thấy {$khoThieu->count()} dòng tồn kho cần tạo serial...");

        $totalCreated = 0;

        DB::transaction(function () use ($khoThieu, &$totalCreated) {
            foreach ($khoThieu as $kho) {
                $soLuong = $kho->soluongtonkho;
                $sp      = $kho->sanPham;

                $masp = $sp ? preg_replace('/[^A-Z0-9]/', '', strtoupper(substr($sp->masp ?? 'SP', 0, 6))) : 'SP';
                if (empty($masp)) {
                    $masp = 'SP' . $kho->ma_sanpham;
                }

                $toInsert = [];
                for ($i = 1; $i <= $soLuong; $i++) {

                    $uniquePart = strtoupper(substr(uniqid('', true), -7));
                    $serial     = strtoupper($masp) . '-CN' . str_pad($kho->ma_chinhanh, 2, '0', STR_PAD_LEFT) . '-' . $uniquePart;
                    $toInsert[] = [
                        'ma_tonkho'      => $kho->id_khoton,
                        'serial_code'    => $serial,
                        'tinhtrang'      => 'nằm trong kho',
                        'min_soluongkho' => $kho->soluongkhothap ?? 5,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                sanpham_serials::insert($toInsert);
                $totalCreated += $soLuong;
            }
        });

        $this->command->info(" Hoàn tất! Đã tạo tổng cộng {$totalCreated} serial mới cho dữ liệu test.");
    }
}
