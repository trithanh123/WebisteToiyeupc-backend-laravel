<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\nhan_vien;
use App\Models\ThongBao;
use App\Models\ThongBaoKhachHang;

class OrderStaffController extends Controller
{
    private function getStaffBranch(Request $request)
    {
        $user = $request->user();
        $nhanVien = nhan_vien::where('id_nguoidung', $user->id_nguoidung)->first();
        return $nhanVien ? $nhanVien->machinhanh : null;
    }

    // Lấy danh sách đơn hàng của chi nhánh nhân viên đang làm
    public function index(Request $request)
    {
        $branchId = $this->getStaffBranch($request);
        if (!$branchId) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chưa được phân công vào chi nhánh nào.'], 403);
        }

        $query = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->leftJoin('thanh_toan', 'don_hang.id_donhang', '=', 'thanh_toan.ma_donhang')
            ->leftJoin('diachi_nguoidung', 'don_hang.ma_diachinguoidung', '=', 'diachi_nguoidung.id_diachinguoidung')
            ->where('don_hang.ma_chinhanh', $branchId)
            ->select(
                'don_hang.id_donhang',
                'don_hang.tongtien',
                'don_hang.phuong_thuc_tt',
                'don_hang.trang_thai_dh',
                'don_hang.thoigiandathang',
                'don_hang.ghichu',
                'nguoi_dung.ten as tenkhachhang',
                'nguoi_dung.sdt as sdt_khach',
                'nguoi_dung.email as email_khach',
                'diachi_nguoidung.ten_nguoinhan',
                'diachi_nguoidung.sdt_nguoinhan',
                'diachi_nguoidung.diachi_chitiet',
                'thanh_toan.trangthai as trangthaithanhtoan',
                'thanh_toan.phuong_thuc as pttt'
            );

        // Filter theo trạng thái
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('don_hang.trang_thai_dh', $request->status);
        }

        // Tìm kiếm theo mã đơn hoặc tên khách
        if ($request->has('search') && $request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('don_hang.id_donhang', 'like', "%{$s}%")
                  ->orWhere('nguoi_dung.ten', 'ilike', "%{$s}%")
                  ->orWhere('nguoi_dung.sdt', 'like', "%{$s}%");
            });
        }

        $orders = $query->orderBy('don_hang.thoigiandathang', 'desc')->paginate(15);

        return response()->json(['status' => 'success', 'data' => $orders]);
    }

    // Chi tiết 1 đơn hàng
    public function show(Request $request, int $id)
    {
        $branchId = $this->getStaffBranch($request);
        if (!$branchId) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền truy cập.'], 403);
        }

        $order = DB::table('don_hang')
            ->join('nguoi_dung', 'don_hang.ma_nguoidung', '=', 'nguoi_dung.id_nguoidung')
            ->leftJoin('diachi_nguoidung', 'don_hang.ma_diachinguoidung', '=', 'diachi_nguoidung.id_diachinguoidung')
            ->leftJoin('thanh_toan', 'don_hang.id_donhang', '=', 'thanh_toan.ma_donhang')
            ->where('don_hang.id_donhang', $id)
            ->where('don_hang.ma_chinhanh', $branchId)
            ->select(
                'don_hang.*',
                'nguoi_dung.ten as tenkhachhang',
                'nguoi_dung.sdt as sdt_khach',
                'nguoi_dung.email as email_khach',
                'diachi_nguoidung.ten_nguoinhan',
                'diachi_nguoidung.sdt_nguoinhan',
                'diachi_nguoidung.diachi_chitiet',
                'thanh_toan.trangthai as trangthaithanhtoan',
                'thanh_toan.phuong_thuc as pttt',
                'thanh_toan.sotien',
                'thanh_toan.ma_giaodich'
            )
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng hoặc đơn không thuộc chi nhánh bạn.'], 404);
        }

        $items = DB::table('chi_tiet_don_hang')
            ->join('san_pham', 'chi_tiet_don_hang.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->where('chi_tiet_don_hang.ma_donhang', $id)
            ->select('chi_tiet_don_hang.*', 'san_pham.tensp', 'san_pham.thumbail')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => ['order' => $order, 'items' => $items]
        ]);
    }

    // Cập nhật trạng thái đơn hàng (nhân viên chỉ được chuyển sang 1 số trạng thái nhất định)
    public function updateStatus(Request $request, int $id)
    {
        $branchId = $this->getStaffBranch($request);
        if (!$branchId) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền.'], 403);
        }

        $request->validate(['trang_thai_dh' => 'required|string']);

        // Nhân viên chỉ được cập nhật những trạng thái hợp lệ
        $allowedTransitions = [
            'Chờ duyệt'      => ['Đang chuẩn bị'],
            'Đang chuẩn bị'  => ['Đang giao hàng'],
            'Đang giao hàng' => ['Đã giao'],
        ];

        $order = DB::table('don_hang')
            ->where('id_donhang', $id)
            ->where('ma_chinhanh', $branchId)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }

        $current  = $order->trang_thai_dh;
        $newState = $request->trang_thai_dh;

        if (!isset($allowedTransitions[$current]) || !in_array($newState, $allowedTransitions[$current])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Không thể chuyển từ \"{$current}\" sang \"{$newState}\"."
            ], 422);
        }

        DB::table('don_hang')->where('id_donhang', $id)->update([
            'trang_thai_dh' => $newState,
            'updated_at'    => now()
        ]);

        // Nếu trạng thái mới là 'Đã giao' và phương thức thanh toán là Tiền mặt (COD), thì tự động đánh dấu đã thanh toán
        if ($newState === 'Đã giao' && strtolower($order->phuong_thuc_tt) === 'tiền mặt') {
            DB::table('thanh_toan')->where('ma_donhang', $id)->update([
                'trangthai'  => 'Đã thanh toán',
                'updated_at' => now()
            ]);
        }

        // Gửi thông báo cho Admin
        ThongBao::create([
            'loai_thong_bao' => 'ORDER',
            'tieu_de'        => 'Cập nhật đơn hàng',
            'noi_dung'       => "Nhân viên đã chuyển đơn hàng #{$id} sang trạng thái: {$newState}",
            'link'           => '/admin/don-hang/' . $id
        ]);

        // Gửi thông báo cho Khách hàng
        ThongBaoKhachHang::create([
            'id_nguoidung'   => $order->ma_nguoidung,
            'loai_thong_bao' => 'don_hang',
            'tieu_de'        => 'Đơn hàng của bạn đã được cập nhật',
            'noi_dung'       => "Đơn hàng #{$id} vừa được chuyển sang: {$newState}.",
            'link'           => '/tai-khoan/don-hang'
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Đã cập nhật trạng thái thành: {$newState}"
        ]);
    }
}
