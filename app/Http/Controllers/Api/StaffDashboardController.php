<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\don_hang;
use App\Models\ton_kho_cuc_bo;
use App\Models\BaoHanh_HoTro;
use App\Models\phieu_dieu_chuyen;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $nhanVien = \App\Models\nhan_vien::where('id_nguoidung', $user->id_nguoidung)->first();
        if (!$nhanVien || !$nhanVien->machinhanh) {
            return response()->json(['message' => 'User does not belong to any branch'], 400);
        }
        $id_chinhanh = $nhanVien->machinhanh;

        $pendingOrders = don_hang::where('ma_chinhanh', $id_chinhanh)
            ->whereIn('trang_thai_dh', ['Chờ xử lý', 'Đang xử lý', 'Đã xác nhận'])
            ->count();
        $lowStock = ton_kho_cuc_bo::where('ma_chinhanh', $id_chinhanh)
            ->where('soluongtonkho', '<', 5)
            ->count();

      
        $warrantyRequests = BaoHanh_HoTro::where('ma_chinhanh', $id_chinhanh)
            ->whereIn('trang_thai', ['Chờ tiếp nhận', 'Đang xử lý'])
            ->count();

      
        $pendingTransfers = phieu_dieu_chuyen::where(function($q) use ($id_chinhanh) {
                $q->where('ma_kho_nhap', $id_chinhanh)
                  ->orWhere('ma_kho_xuat', $id_chinhanh);
            })
            ->whereIn('trang_thai', ['Chờ duyệt', 'Đã duyệt']) 
            ->count();

       
        $latestOrders = don_hang::with(['nguoiDung', 'diaChi'])
            ->where('ma_chinhanh', $id_chinhanh)
            ->whereIn('trang_thai_dh', ['Chờ xử lý', 'Đang xử lý', 'Đã xác nhận'])
            ->orderBy('thoigiandathang', 'desc')
            ->take(5)
            ->get();

       
        $recentTransfers = phieu_dieu_chuyen::with(['khoXuat', 'khoNhap', 'nguoiTao'])
            ->where(function($q) use ($id_chinhanh) {
                $q->where('ma_kho_nhap', $id_chinhanh)
                  ->orWhere('ma_kho_xuat', $id_chinhanh);
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
        $recentWarranties = BaoHanh_HoTro::with(['nguoiDung'])
            ->where('ma_chinhanh', $id_chinhanh)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return response()->json([
            'stats' => [
                'pendingOrders' => $pendingOrders,
                'lowStock' => $lowStock,
                'warrantyRequests' => $warrantyRequests,
                'pendingTransfers' => $pendingTransfers,
            ],
            'latestOrders' => $latestOrders,
            'recentActivities' => [
                'transfers' => $recentTransfers,
                'warranties' => $recentWarranties,
            ]
        ], 200);
    }
}
