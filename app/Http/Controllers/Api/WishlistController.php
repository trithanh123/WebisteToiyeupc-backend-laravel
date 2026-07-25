<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SanPhamYeuThich;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $wishlist = DB::table('sanpham_yeuthich')
            ->join('san_pham', 'sanpham_yeuthich.id_sanpham', '=', 'san_pham.id_sanpham')
            ->where('sanpham_yeuthich.id_nguoidung', $user->id_nguoidung)
            ->select('san_pham.*', 'sanpham_yeuthich.created_at as liked_at')
            ->orderBy('sanpham_yeuthich.created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $wishlist
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'id_sanpham' => 'required|integer'
        ]);

        $user = $request->user();
        
        $existing = SanPhamYeuThich::where('id_nguoidung', $user->id_nguoidung)
            ->where('id_sanpham', $request->id_sanpham)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status' => 'success',
                'action' => 'removed',
                'message' => 'Đã bỏ yêu thích sản phẩm'
            ]);
        }

        SanPhamYeuThich::create([
            'id_nguoidung' => $user->id_nguoidung,
            'id_sanpham' => $request->id_sanpham
        ]);

        return response()->json([
            'status' => 'success',
            'action' => 'added',
            'message' => 'Đã thêm vào danh sách yêu thích'
        ]);
    }
}
