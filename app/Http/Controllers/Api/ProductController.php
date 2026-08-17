<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\san_pham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\danh_muc;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\StorePRoductRequest;
use Illuminate\Support\Facades\Log;
class ProductController extends Controller
{
    public function index(Request $request)
    {   
        $branchId = $request->query('branch_id');
        $query = san_pham::with(['danhMuc:id_danhmuc,ten_danhmuc,slug']);
        if (!$request->routeIs('admin.*')) {
            $query->where('trangthai', 1);
        }
        if ($branchId) {
            $query->whereHas('tonKho', function($q) use ($branchId) {
                $q->where('ma_chinhanh', $branchId)
                  ->where('soluongtonkho', '>', 0);
            });
        }
        $products = $query->orderBy('id_sanpham', 'desc')
            ->get()
            ->map(function ($product) {
                $product->ten_danhmuc = $product->danhMuc ? $product->danhMuc->ten_danhmuc : 'Chưa phân loại';
                unset($product->danhMuc);
                return $product;
            });
        return response()->json([
            'status' => 'success',
            'total'  => $products->count(),
            'data'   => $products,
        ], 200);
    }

    public function byCategory(Request $request)
    {
        $slugOrId  = $request->query('danh-muc');
        $branchId  = $request->query('branch_id');
        $sort      = $request->query('sort', 'newest');
        $minPrice  = $request->query('min_price', 0);
        $maxPrice  = $request->query('max_price', 999999999);
        $perPage   = (int) $request->query('per_page', 16);
        $category = null;
        if ($slugOrId) {
            $category = danh_muc::where('slug', $slugOrId)
                ->orWhere('id_danhmuc', is_numeric($slugOrId) ? $slugOrId : 0)
                ->first();
        }
        $categoryIds = [];
        $breadcrumb = [];
        if ($category) {
            $categoryIds[] = $category->id_danhmuc;


            $children = danh_muc::where('danhmuc_cha', $category->id_danhmuc)->pluck('id_danhmuc')->toArray();
            $categoryIds = array_merge($categoryIds, $children);

        
            if (!empty($children)) {
                $grandChildren = danh_muc::whereIn('danhmuc_cha', $children)->pluck('id_danhmuc')->toArray();
                $categoryIds = array_merge($categoryIds, $grandChildren);
            }


            $breadcrumb[] = [
                'id_danhmuc'  => $category->id_danhmuc,
                'ten_danhmuc' => $category->ten_danhmuc,
                'slug'        => $category->slug,
            ];

           
            $ancestor = $category;
            while ($ancestor->danhmuc_cha) {
                $parent = danh_muc::find($ancestor->danhmuc_cha);
                if ($parent) {
                    $categoryIds[] = $parent->id_danhmuc;
                    $breadcrumb[] = [
                        'id_danhmuc'  => $parent->id_danhmuc,
                        'ten_danhmuc' => $parent->ten_danhmuc,
                        'slug'        => $parent->slug,
                    ];
                    $ancestor = $parent;
                } else {
                    break;
                }
            }

            $categoryIds = array_unique($categoryIds);
            $breadcrumb = array_reverse($breadcrumb); 
        }
        $query = san_pham::with(['danhMuc:id_danhmuc,ten_danhmuc,slug'])->where('trangthai', 1);
        if (!empty($categoryIds)) {
            $query->whereIn('ma_danhmuc', $categoryIds);
        }

        if ($branchId) {
            $query->whereHas('tonKho', function($q) use ($branchId) {
                $q->where('ma_chinhanh', $branchId)
                  ->where('soluongtonkho', '>', 0);
            });
        }
        $query->whereBetween('gia', [(int) $minPrice, (int) $maxPrice]);
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('gia', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('gia', 'desc');
                break;
            case 'oldest':
                $query->orderBy('id_sanpham', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('id_sanpham', 'desc');
                break;
        }

        $products = $query->paginate($perPage);

        return response()->json([
            'status'     => 'success',
            'category'   => $category ? [
                'id_danhmuc'  => $category->id_danhmuc,
                'ten_danhmuc' => $category->ten_danhmuc,
                'slug'        => $category->slug,
            ] : null,
            'breadcrumb' => $breadcrumb,
            'total'      => $products->total(),
            'per_page'   => $products->perPage(),
            'current_page' => $products->currentPage(),
            'last_page'  => $products->lastPage(),
            'data'       => $products->items(),
        ], 200);
    }

  
    public function show($id)
    {
        $product = san_pham::with(['danhMuc', 'tonKho.chiNhanh'])->find($id);
        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy sản phẩm với ID = ' . $id,
            ], 404);
        }
        $breadcrumb = [];
        if ($product->danhMuc) {
            $category = $product->danhMuc;
            $breadcrumb[] = [
                'id_danhmuc'  => $category->id_danhmuc,
                'ten_danhmuc' => $category->ten_danhmuc,
                'slug'        => $category->slug,
            ];
            $ancestor = $category;
            while ($ancestor->danhmuc_cha) {
                $parent = danh_muc::find($ancestor->danhmuc_cha);
                if ($parent) {
                    $breadcrumb[] = [
                        'id_danhmuc'  => $parent->id_danhmuc,
                        'ten_danhmuc' => $parent->ten_danhmuc,
                        'slug'        => $parent->slug,
                    ];
                    $ancestor = $parent;
                } else {
                    break;
                }
            }
            $breadcrumb = array_reverse($breadcrumb);
        }
        return response()->json([
            'status'     => 'success',
            'data'       => $product,
            'breadcrumb' => $breadcrumb,
        ], 200);
    }

   
    public function store(StorePRoductRequest $request)
    {
        $product = san_pham::create($request->validated());
        
        try {
            $this->updateProductEmbedding($product);
        } catch (\Exception $e) {
            Log::warning('[Embedding] Không thể tạo embedding cho sản phẩm ' . $product->id_sanpham . ': ' . $e->getMessage());
        }
        
        $product->load('danhMuc:id_danhmuc,ten_danhmuc');
        return response()->json([
            'status'  => 'success',
            'message' => 'Thêm sản phẩm thành công!',
            'data'    => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = san_pham::find($id);
        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy sản phẩm với ID = ' . $id,
            ], 404);
        }
        $product->update($request->validated());
        
        try {
            $this->updateProductEmbedding($product);
        } catch (\Exception $e) {
            Log::warning('[Embedding] Không thể cập nhật embedding cho sản phẩm ' . $product->id_sanpham . ': ' . $e->getMessage());
        }
        
        $product->load('danhMuc:id_danhmuc,ten_danhmuc');
        return response()->json([
            'status'  => 'success',
            'message' => 'Cập nhật sản phẩm thành công!',
            'data'    => $product,
        ], 200);
    }
    


    public function destroy(Request $request, $id)
    {
        $product = san_pham::find($id);
        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }
        $productId = $product->id_sanpham;
        $product->delete();
        try {
            $pythonServiceUrl = config('services.python.search_url');
            // Tăng timeout lên 30s để tránh embedding bị stale khi Python service cold-start (Render)
            Http::timeout(30)->delete("{$pythonServiceUrl}/delete/{$productId}");
        } catch (\Exception $e) {
            Log::warning('[Embedding] Không thể xóa embedding sản phẩm id=' . $productId . ': ' . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa sản phẩm thành công!',
        ], 200);
    }
    
    public function toggleStatus($id)
    {
        $product = san_pham::find($id);
        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy sản phẩm với ID = ' . $id,
            ], 404);
        }
        $product->trangthai = !$product->trangthai;
        $product->save();
        return response()->json([
            'status'  => 'success',
            'message' => $product->trangthai ? 'Sản phẩm đã được kinh doanh trở lại!' : 'Sản phẩm đã bị ngừng kinh doanh!',
        ], 200);
    }

    public function uploadImage(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['message' => 'No image file provided'], 400);
        }

        $file = $request->file('image');
        
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $uploadPreset = env('CLOUDINARY_UPLOAD_PRESET');

        if (!$cloudName || !$uploadPreset) {
            return response()->json(['message' => 'Cloudinary is not configured. Please add CLOUDINARY_CLOUD_NAME and CLOUDINARY_UPLOAD_PRESET to .env'], 500);
        }

        $response =Http::attach(
            'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            'upload_preset' => $uploadPreset,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
                'url' => $data['secure_url']
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to upload image to Cloudinary',
            'error' => $response->json()
        ], 500);
    }



    public function aiSearch(Request $request)
    {
        $queryText = $request->input('query');
        $branchId  = $request->input('branch_id');
        $topK      = $request->input('top_k', 10);
        
        if (!$queryText) {
            return response()->json(['data' => []]);
        }
        try {
            $pythonServiceUrl = config('services.python.search_url');
            $searchResponse   = Http::timeout(95)->post("{$pythonServiceUrl}/search", [
                'query'     => $queryText,
                'branch_id' => $branchId,
                'top_k'     => $topK * 3, // Oversampling (lấy nhiều hơn để trừ hao DB filter)
            ]);

            if (!$searchResponse->successful()) {
                Log::error('[Qdrant] Python service trả về lỗi: ' . $searchResponse->body());
                return response()->json(['error' => 'Lỗi kết nối Search Service'], 500);
            }
        } catch (\Exception $e) {
            Log::error('[Qdrant] Không kết nối được Python service: ' . $e->getMessage());
            return response()->json(['error' => 'Search Service không khả dụng. Vui lòng thử lại sau.'], 503);
        }

        $searchData = $searchResponse->json();
        $results    = $searchData['results'] ?? [];

        if (empty($results)) {
            return response()->json([
                'query'    => $queryText,
                'semantic' => $searchData['semantic'] ?? $queryText,
                'filters'  => $searchData['filters'] ?? [],
                'data'     => [],
            ]);
        }

        $orderedIds = array_column($results, 'id');
        $scoreMap   = array_column($results, 'score', 'id');
        $filters    = $searchData['filters'] ?? [];

        $dbQuery = san_pham::with(['tonKho' => function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('ma_chinhanh', $branchId);
            }
        }])
        ->where('trangthai', 1)
        ->select('id_sanpham', 'masp', 'tensp', 'gia', 'thumbail', 'motasanpham', 'specifications')
        ->whereIn('id_sanpham', $orderedIds);

        // Lọc nghiêm ngặt PC hoặc Laptop dựa trên từ khóa người dùng
        $lowerQuery = mb_strtolower($queryText);
        $isPcQuery = preg_match('/\b(pc|máy tính|máy tính để bàn)\b/u', $lowerQuery) && !preg_match('/\blaptop\b/u', $lowerQuery);
        $isLaptopQuery = preg_match('/\blaptop\b/u', $lowerQuery) && !preg_match('/\b(pc|máy tính để bàn)\b/u', $lowerQuery);

        if ($isPcQuery) {
            $dbQuery->where(function($query) {
                $query->whereDoesntHave('danhMuc', function ($q) {
                    $q->where('ten_danhmuc', 'like', '%Laptop%');
                })->where('tensp', 'not like', '%Laptop%');
            });
        } elseif ($isLaptopQuery) {
            $dbQuery->where(function($query) {
                $query->whereHas('danhMuc', function ($q) {
                    $q->where('ten_danhmuc', 'like', '%Laptop%');
                })->orWhere('tensp', 'like', '%Laptop%');
            });
        }

        // Double-check filter giá từ Python (phòng trường hợp Qdrant payload stale)
        if (!empty($filters['gia_lte'])) {
            $dbQuery->where('gia', '<=', (int)$filters['gia_lte']);
        }
        if (!empty($filters['gia_gte'])) {
            $dbQuery->where('gia', '>=', (int)$filters['gia_gte']);
        }

        if ($branchId) {
            $dbQuery->whereHas('tonKho', function ($q) use ($branchId) {
                $q->where('ma_chinhanh', $branchId)->where('soluongtonkho', '>', 0);
            });
        }
        $productsById = $dbQuery->get()->keyBy('id_sanpham');
        $products = collect($orderedIds)
            ->filter(fn($id) => isset($productsById[$id]))
            ->map(function ($id) use ($productsById, $scoreMap) {
                $p = $productsById[$id]->toArray();
                $p['ai_score'] = $scoreMap[$id] ?? 0;
                return $p;
            })
            ->take($topK)
            ->values();


        return response()->json([
            'query'    => $queryText,
            'semantic' => $searchData['semantic'] ?? $queryText,
            'filters'  => $searchData['filters']  ?? [],
            'data'     => $products,
        ]);
    }
    private function updateProductEmbedding(san_pham $product): void
    {
        $pythonServiceUrl = config('services.python.search_url');
        
        $product->load('danhMuc:id_danhmuc,ten_danhmuc');
        
        Http::timeout(95)->post("{$pythonServiceUrl}/upsert", [
            'id'          => $product->id_sanpham,
            'masp'        => $product->masp,
            'tensp'       => $product->tensp,
            'gia'         => $product->gia,
            'motasanpham' => $product->motasanpham,
            'ten_danhmuc' => $product->danhMuc?->ten_danhmuc,
            'specifications' => $product->specifications,
        ]);
    }
    public function checkStock($id, Request $request)
    {
        if ($id === 'undefined' || !$id) {
            return response()->json([
                'is_available' => false,
                'stock' => 0,
                'message' => 'ID không hợp lệ'
            ], 200);
        }

        $product = san_pham::with(['tonKho.chiNhanh'])->find($id);

        if (!$product) {
            return response()->json([
                'is_available' => false,
                'stock' => 0,
                'message' => 'Sản phẩm không tồn tại'
            ], 200);
        }

        $otherBranches = [];
        if ($request->has('branch_id') && $request->branch_id) {
            $branchId = (int) $request->branch_id;
            $totalStock = $product->tonKho->where('ma_chinhanh', $branchId)->sum('soluongtonkho');
            $otherBranches = $product->tonKho->where('ma_chinhanh', '!=', $branchId)
                ->where('soluongtonkho', '>', 0)
                ->map(function ($tk) {
                    return [
                        'id_chinhanh' => $tk->ma_chinhanh,
                        'ten_chinhanh' => $tk->chiNhanh ? $tk->chiNhanh->ten_chinhanh : 'Chi nhánh ID ' . $tk->ma_chinhanh,
                        'stock' => $tk->soluongtonkho
                    ];
                })->values();
        } else {
            $totalStock = $product->tonKho->sum('soluongtonkho');
        }

        return response()->json([
            'is_available' => $totalStock > 0,
            'stock' => $totalStock,
            'other_branches' => $otherBranches
        ], 200);
    }

    public function buildPc(Request $request)
    {
        $query = $request->input('query');
        if (empty($query)) {
            return response()->json(['message' => 'Vui lòng nhập câu hỏi build máy.'], 400);
        }
        
        $pythonServiceUrl = config('services.python.search_url');
        
        try {
            $response = Http::timeout(60)->post("{$pythonServiceUrl}/ai-build-pc", [
                'query' => $query
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Get fresh prices and stock from MySQL for each component.
                if (isset($data['build']) && is_array($data['build'])) {
                    $build = [];
                    foreach ($data['build'] as $item) {
                        $masp      = $item['masp']       ?? null;
                        $qdrantId  = $item['id_sanpham'] ?? null;
                        $product   = null;

                        // 1️⃣ Tìm theo masp (product code)
                        if ($masp) {
                            $product = san_pham::with(['danhMuc', 'tonKho' => function ($q) {
                                $q->where('ma_chinhanh', request()->header('Branch-Id') ?? 1);
                            }])->where('masp', $masp)->where('trangthai', 1)->first();
                        }

                        // 2️⃣ Fallback: tìm theo id_sanpham từ Qdrant
                        if (!$product && $qdrantId) {
                            $product = san_pham::with(['danhMuc', 'tonKho' => function ($q) {
                                $q->where('ma_chinhanh', request()->header('Branch-Id') ?? 1);
                            }])->where('trangthai', 1)->find($qdrantId);
                        }

                        // 3️⃣ Chỉ thêm vào build nếu sản phẩm tồn tại trong MySQL
                        //    (bỏ qua sản phẩm Qdrant cũ đã bị xóa khỏi DB)
                        if ($product) {
                            $build[] = $product;
                        }
                        // else: skip – tránh lỗi 404 khi user click vào sản phẩm không tồn tại
                    }
                    $data['build'] = $build;
                }
                
                return response()->json($data);
            }
            
            // If python service returns 404, it means no build found.
            if ($response->status() == 404) {
                return response()->json(['message' => 'Không tìm thấy cấu hình phù hợp với ngân sách và yêu cầu.'], 404);
            }
            
            // Otherwise, it's a server error (e.g. 502 Bad Gateway from Render cold start)
            return response()->json(['message' => 'AI Service đang khởi động hoặc quá tải, vui lòng thử lại sau vài giây! (Mã lỗi: ' . $response->status() . ')'], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi kết nối đến Python AI Service: ' . $e->getMessage()
            ], 500);
        }
    }
}
