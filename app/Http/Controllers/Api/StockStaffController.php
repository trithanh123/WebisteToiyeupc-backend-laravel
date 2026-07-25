<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\nhan_vien;
use App\Models\chi_nhanh;
use App\Models\ton_kho_cuc_bo;
use Illuminate\Support\Facades\Validator;

class StockStaffController extends Controller
{
    private function getStaffBranch(Request $request)
    {
        $user = $request->user();
        $nhanVien = nhan_vien::where('id_nguoidung', $user->id_nguoidung)->first();
        if (!$nhanVien || !$nhanVien->machinhanh) {
            return null;
        }

        $chiNhanh = chi_nhanh::where('id_chinhanh', $nhanVien->machinhanh)->first();
        return $chiNhanh;
    }

    public function index(Request $request)
    {
        $chiNhanh = $this->getStaffBranch($request);
        
        if (!$chiNhanh) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn chưa được phân công vào chi nhánh nào hoặc chi nhánh không tồn tại.'
            ], 403);
        }

        $stocks = ton_kho_cuc_bo::with(['sanPham:id_sanpham,tensp,thumbail,gia'])
            ->where('ma_chinhanh', $chiNhanh->id_chinhanh)
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'branch' => $chiNhanh->ten_chinhanh,
            'data' => $stocks
        ], 200);
    }

    public function update(Request $request, $id_khoton)
    {
        $chiNhanh = $this->getStaffBranch($request);
        
        if (!$chiNhanh) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền thao tác trên chi nhánh này.'
            ], 403);
        }

        $tonKho = ton_kho_cuc_bo::where('ma_chinhanh', $chiNhanh->id_chinhanh)
            ->find($id_khoton);

        if (!$tonKho) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy thông tin kho cho sản phẩm này tại chi nhánh của bạn.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'soluongtonkho' => 'required|integer|min:0',
            'soluongkhothap' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tonKho->soluongtonkho = $request->soluongtonkho;
        if ($request->has('soluongkhothap')) {
            $tonKho->soluongkhothap = $request->soluongkhothap;
        }
        $tonKho->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Cập nhật tồn kho thành công.',
            'data'    => $tonKho
        ], 200);
    }

    public function warehouseOverview(Request $request)
    {
        $search = $request->query('search', '');
        $branchId = $request->query('branch', '');
        $query = ton_kho_cuc_bo::with([
            'sanPham:id_sanpham,tensp,thumbail,gia',
            'chiNhanh:id_chinhanh,ten_chinhanh,diachi_chitiet',
        ]);

        if ($branchId) {
            $query->where('ma_chinhanh', $branchId);
        }

        if ($search) {
            $query->whereHas('sanPham', function ($q) use ($search) {
                $q->where('tensp', 'ilike', "%{$search}%");
            });
        }
        $data = $query->orderBy('ma_chinhanh')->get();
        $grouped = $data->groupBy('ma_chinhanh')->map(function ($items) {
            $branch = $items->first()->chiNhanh;
            return [
                'id_chinhanh'   => $branch?->id_chinhanh,
                'ten_chinhanh'  => $branch?->ten_chinhanh,
                'diachi'        => $branch?->diachi_chitiet,
                'tong_loai_sp'  => $items->count(),
                'tong_ton_kho'  => $items->sum('soluongtonkho'),
                'san_phams'     => $items->values(),
            ];
        })->values();
        return response()->json([
            'status' => 'success',
            'data'   => $grouped,
        ], 200);
    }
}
