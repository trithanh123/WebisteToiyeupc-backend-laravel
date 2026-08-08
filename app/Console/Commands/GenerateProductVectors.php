<?php

namespace App\Console\Commands;

use App\Models\san_pham;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateProductVectors extends Command
{
    protected $signature   = 'qdrant:index {--all : Re-index tất cả sản phẩm, kể cả đã index rồi}';
    protected $description = 'Index sản phẩm vào Qdrant Cloud qua Python Search Service';

    public function handle(): int
    {
        $pythonServiceUrl = env('PYTHON_SEARCH_URL', 'http://localhost:8001');

        try {
            $health = Http::timeout(5)->get("{$pythonServiceUrl}/health");
            if (!$health->successful()) {
                $this->error("Python Search Service không phản hồi tại {$pythonServiceUrl}");
                $this->error("Hãy chạy: uvicorn main:app --reload --port 8001");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Không kết nối được Python Service: " . $e->getMessage());
            return 1;
        }

        $this->info(" Python Service đang chạy tại {$pythonServiceUrl}");
        $products = san_pham::with('danhMuc:id_danhmuc,ten_danhmuc')
            ->get(['id_sanpham', 'masp', 'tensp', 'gia', 'motasanpham', 'specifications', 'ma_danhmuc']);

        if ($products->isEmpty()) {
            $this->info('Không có sản phẩm nào để index.');
            return 0;
        }

        $total   = $products->count();
        $success = 0;
        $failed  = 0;

        $this->info("Đang index {$total} sản phẩm vào Qdrant...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products->chunk(50) as $chunk) {
            $batchData = [];
            foreach ($chunk as $product) {
                $specs = $product->specifications;
                if (is_string($specs)) {
                    $specs = json_decode($specs, true) ?? [];
                }
                $batchData[] = [
                    'id'             => $product->id_sanpham,
                    'masp'           => $product->masp,
                    'tensp'          => $product->tensp,
                    'gia'            => (int) $product->gia,
                    'motasanpham'    => $product->motasanpham ?? '',
                    'specifications' => $specs ?? [],
                    'ten_danhmuc'    => $product->danhMuc->ten_danhmuc ?? '',
                ];
            }

            try {
                $response = Http::timeout(60)->post("{$pythonServiceUrl}/upsert-batch", [
                    'products' => $batchData
                ]);

                if ($response->successful()) {
                    $success += $chunk->count();
                } else {
                    Log::error("qdrant:index — Lỗi index batch: " . $response->body());
                    $failed += $chunk->count();
                }
            } catch (\Exception $e) {
                Log::error("qdrant:index — Exception batch: " . $e->getMessage());
                $failed += $chunk->count();
            }

            $bar->advance($chunk->count());
        }

        $bar->finish();
        $this->newLine();
        $this->info("Hoàn tất. Thành công: {$success}, Thất bại: {$failed}.");

        return $failed > 0 ? 1 : 0;
    }
}
