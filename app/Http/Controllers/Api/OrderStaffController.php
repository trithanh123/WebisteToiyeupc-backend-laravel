<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\nhan_vien;
use App\Models\ThongBao;
use App\Models\ThongBaoKhachHang;
use App\Models\ChiTietDonHangSerial;
use App\Http\Requests\OrderStaffRequest;
class OrderStaffController extends Controller
{
    private function getStaffBranch(Request $request)
    {
        $user = $request->user();
        $nhanVien = nhan_vien::where('id_nguoidung', $user->id_nguoidung)->first();
        return $nhanVien ? $nhanVien->machinhanh : null;
    }

  
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

       
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('don_hang.trang_thai_dh', $request->status);
        }


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

        foreach ($items as $item) {
            $khoton = DB::table('ton_kho_cuc_bo')
                ->where('ma_sanpham', $item->ma_sanpham)
                ->where('ma_chinhanh', $branchId)
                ->first();
                
            if ($khoton) {
                $item->available_serials = DB::table('sanpham_serials')
                    ->where('ma_tonkho', $khoton->id_khoton)
                    ->whereIn('tinhtrang', ['nằm trong kho', 'trong kho'])
                    ->select('id_serial', 'serial_code')
                    ->get();
            } else {
                $item->available_serials = [];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => ['order' => $order, 'items' => $items]
        ]);
    }

   
    public function updateStatus(OrderStaffRequest $request, int $id)
    {
        $branchId = $this->getStaffBranch($request);
        if (!$branchId) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền.'], 403);
        }

        $request->validate(['trang_thai_dh' => 'required|string']);

        $allowedTransitions = [
            'Chờ duyệt'      => ['Đang chuẩn bị'],
            'Đang chuẩn bị'  => ['Đang giao hàng'],
            'Đang giao hàng' => ['Đã giao', 'Giao thất bại'],
            'Đang giao'      => ['Đã giao', 'Giao thất bại'],
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
        
        try {
            DB::beginTransaction();
        
        if ($newState === 'Đang giao hàng') {
            foreach ($request->serials as $item) {
                ChiTietDonHangSerial::create([
                    'ma_chitietdh' => $item['id_chitietdh'],
                    'ma_serial'    => $item['id_serial']
                ]);
                DB::table('sanpham_serials')
                    ->where('id_serial', $item['id_serial'])
                    ->update([
                        'tinhtrang'  => 'đã bán', 
                        'updated_at' => now()
                    ]);
            }
        }

        if ($newState === 'Giao thất bại') {
            // Restore local stock
            $items = DB::table('chi_tiet_don_hang')->where('ma_donhang', $id)->get();
            foreach ($items as $item) {
                DB::table('ton_kho_cuc_bo')
                    ->where('ma_chinhanh', $branchId)
                    ->where('ma_sanpham', $item->ma_sanpham)
                    ->increment('soluongtonkho', $item->soluong);
            }

            // Revert serials
            $serials = DB::table('chi_tiet_don_hang__serial')
                ->join('chi_tiet_don_hang', 'chi_tiet_don_hang.id_chitietdh', '=', 'chi_tiet_don_hang__serial.ma_chitietdh')
                ->where('chi_tiet_don_hang.ma_donhang', $id)
                ->pluck('chi_tiet_don_hang__serial.ma_serial');
            
            if ($serials->isNotEmpty()) {
                DB::table('sanpham_serials')
                    ->whereIn('id_serial', $serials)
                    ->update([
                        'tinhtrang' => 'nằm trong kho',
                        'updated_at' => now()
                    ]);
                
                // Delete mapping
                DB::table('chi_tiet_don_hang__serial')
                    ->whereIn('ma_serial', $serials)
                    ->delete();
            }

            // Mark payment as failed
            DB::table('thanh_toan')->where('ma_donhang', $id)->update([
                'trangthai'  => 'Thất bại',
                'updated_at' => now()
            ]);
        }

        DB::table('don_hang')->where('id_donhang', $id)->update([
            'trang_thai_dh' => $newState,
            'updated_at'    => now()
        ]);

        if ($newState === 'Đã giao' && strtolower($order->phuong_thuc_tt) === 'tiền mặt') {
            DB::table('thanh_toan')->where('ma_donhang', $id)->update([
                'trangthai'  => 'Đã thanh toán',
                'updated_at' => now()
            ]);
        }
        ThongBao::create([
            'loai_thong_bao' => 'ORDER',
            'tieu_de'        => 'Cập nhật đơn hàng',
            'noi_dung'       => "Nhân viên đã chuyển đơn hàng #{$id} sang trạng thái: {$newState}",
            'link'           => '/admin/don-hang/' . $id
        ]);
        ThongBaoKhachHang::create([
            'id_nguoidung'   => $order->ma_nguoidung,
            'loai_thong_bao' => 'don_hang',
            'tieu_de'        => 'Đơn hàng của bạn đã được cập nhật',
            'noi_dung'       => "Đơn hàng #{$id} vừa được chuyển sang: {$newState}.",
            'link'           => '/tai-khoan/don-hang'
        ]);

        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => "Đã cập nhật trạng thái thành: {$newState}"
        ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
