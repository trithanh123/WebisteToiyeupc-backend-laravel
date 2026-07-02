<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\danh_muc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = danh_muc::with(['danhMucCon.danhMucCon'])
            ->whereNull('danhmuc_cha')   
            ->where('is_active', true)   
            ->orderBy('id_danhmuc', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'total'  => $categories->count(),
            'data'   => $categories,
        ], 200);
    }
    public function all()
    {
        $categories = danh_muc::orderBy('id_danhmuc', 'asc')->get([
            'id_danhmuc',
            'ten_danhmuc',
            'slug',
            'danhmuc_cha',
            'hinhanh_icon',
            'is_active',
        ]);

        return response()->json([
            'status' => 'success',
            'total'  => $categories->count(),
            'data'   => $categories,
        ], 200);
    }
    public function show($id)
    {
        $category = danh_muc::with(['danhMucCha', 'danhMucCon', 'sanPham'])
            ->find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy danh mục với ID = ' . $id,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $category,
        ], 200);
    }
    public function store(StoreCategoryRequest $request)
    {
        $category = danh_muc::create([
            'ten_danhmuc'  => $request->ten_danhmuc,
            'slug'         => $request->slug,
            'danhmuc_cha'  => $request->danhmuc_cha ?? null,
            'hinhanh_icon' => $request->hinhanh_icon ?? null,
            'is_active'    => $request->is_active ?? true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Thêm danh mục mới thành công!',
            'data'    => $category,
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        
    $category = danh_muc::find($id);

    if (!$category) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Không tìm thấy danh mục với ID = ' . $id,
        ], 404);
    }
    $category->update($request->validated());
    return response()->json([
        'status'  => 'success',
        'message' => 'Cập nhật danh mục thành công!',
        'data'    => $category,
    ], 200);
    }
    public function destroy(Request $request, $id)
    {
        $category = danh_muc::find($id);
        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy danh mục với ID = ' . $id,
            ], 404);
        }
        $countCon = danh_muc::where('danhmuc_cha', $id)->count();
        if ($countCon > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không thể xóa! Danh mục này đang có ' . $countCon . ' danh mục con. Vui lòng xóa hoặc chuyển các danh mục con trước.',
            ], 409);
        }
        $countSanPham = $category->sanPham()->count();
        if ($countSanPham > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không thể xóa! Danh mục này đang chứa ' . $countSanPham . ' sản phẩm. Vui lòng chuyển sản phẩm sang danh mục khác trước.',
            ], 409);
        }
        $ten = $category->ten_danhmuc;
        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa danh mục "' . $ten . '" thành công!',
        ], 200);
    }
    public function toggle(Request $request, $id)
    {
        $category = danh_muc::find($id);
        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy danh mục với ID = ' . $id,
            ], 404);
        }
        $category->is_active = !$category->is_active;
        $category->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Đã ' . ($category->is_active ? 'kích hoạt' : 'ẩn') . ' danh mục "' . $category->ten_danhmuc . '".',
            'data'    => [
                'id_danhmuc' => $category->id_danhmuc,
                'is_active'  => $category->is_active,
            ],
        ], 200);
    }
}
