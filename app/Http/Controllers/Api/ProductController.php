<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\san_pham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\danh_muc;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\StorePRoductRequest;
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch_id');
        $query = san_pham::with(['danhMuc:id_danhmuc,ten_danhmuc,slug']);
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
        $query = san_pham::with(['danhMuc:id_danhmuc,ten_danhmuc,slug']);
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
        
       
        $this->updateProductEmbedding($product);
        
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
        
        
        $this->updateProductEmbedding($product);
        
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
        $product->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa sản phẩm thành công!',
        ], 200);
    }


    public function aiSearch(Request $request)
    {
        $queryText = $request->input('query');
        $branchId  = $request->input('branch_id');

        if (!$queryText) {
            return response()->json(['data' => []]);
        }
        try {
            $pythonServiceUrl = env('PYTHON_SEARCH_URL', 'http://localhost:8001');
            $searchResponse   = Http::timeout(15)->post("{$pythonServiceUrl}/search", [
                'query'     => $queryText,
                'branch_id' => $branchId,
                'top_k'     => 5,
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
        $dbQuery = san_pham::with(['tonKho' => function ($q) use ($branchId) {
            if ($branchId) {
                $q->where('ma_chinhanh', $branchId);
            }
        }])
        ->select('id_sanpham', 'masp', 'tensp', 'gia', 'thumbail', 'motasanpham', 'specifications')
        ->whereIn('id_sanpham', $orderedIds);
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
            ->values();

        return response()->json([
            'query'    => $queryText,
            'semantic' => $searchData['semantic'] ?? $queryText,
            'filters'  => $searchData['filters']  ?? [],
            'data'     => $products,
        ]);
    }
}
