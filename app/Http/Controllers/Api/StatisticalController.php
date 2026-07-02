<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\don_hang;
use App\Models\nguoi_dung;
use App\Models\ton_kho_cuc_bo;
use App\Models\chi_tiet_don_hang;
use Carbon\Carbon;
class StatisticalController extends Controller
{
    public function dashboard(Request $request)
    {
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);
        $doanhThu = DB::table('don_hang')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('trang_thai_dh', '!=', 'Đã hủy')
            ->sum('tongtien');
        $soDonHang = DB::table('don_hang')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count('id_donhang');
        $khachHangMoi = DB::table('nguoi_dung')
            ->where('phanquyen', 3) 
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count('id_nguoidung');
        if ($khachHangMoi == 0) {
            $khachHangMoi = DB::table('nguoi_dung')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count('id_nguoidung');
        }
        $topSanPham = DB::table('chi_tiet_don_hang')
            ->join('don_hang', 'chi_tiet_don_hang.ma_donhang', '=', 'don_hang.id_donhang')
            ->join('san_pham', 'chi_tiet_don_hang.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->select('san_pham.id_sanpham', 'san_pham.tensp', 'san_pham.masp', 'san_pham.thumbail', DB::raw('SUM("chi_tiet_don_hang"."soluong") as total_sold'))
            ->whereMonth('don_hang.created_at', $month)
            ->whereYear('don_hang.created_at', $year)
            ->where('don_hang.trang_thai_dh', '!=', 'Đã hủy')
            ->groupBy('san_pham.id_sanpham', 'san_pham.tensp', 'san_pham.masp', 'san_pham.thumbail')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
        $sapHetHang = DB::table('ton_kho_cuc_bo')
            ->join('san_pham', 'ton_kho_cuc_bo.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->join('chi_nhanh', 'ton_kho_cuc_bo.ma_chinhanh', '=', 'chi_nhanh.id_chinhanh')
            ->select(
                'san_pham.tensp', 
                'san_pham.thumbail',
                'san_pham.masp',
                'chi_nhanh.ten_chinhanh',
                'ton_kho_cuc_bo.soluongtonkho',
                'ton_kho_cuc_bo.soluongkhothap'
            )
            ->whereColumn('ton_kho_cuc_bo.soluongtonkho', '<=', 'ton_kho_cuc_bo.soluongkhothap')
            ->orderBy('ton_kho_cuc_bo.soluongtonkho', 'asc')
            ->limit(10) 
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'thang' => $month,
                'nam' => $year,
                'tong_doanh_thu' => $doanhThu,
                'tong_don_hang' => $soDonHang,
                'khach_hang_moi' => $khachHangMoi,
                'top_san_pham' => $topSanPham,
                'sap_het_hang' => $sapHetHang
            ]
        ]);
    }
}
