<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaoHanh_HoTro;
use App\Models\nhan_vien;
use App\Models\ThongBao;
use App\Models\ThongBaoKhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportWarrantyController extends Controller
{

    private function getStaffInfo(Request $request): ?object
    {
        $user = $request->user();
        return nhan_vien::where('id_nguoidung', $user->id_nguoidung)->first();
    }

    public function index(Request $request)
    {
        $query = DB::table('baohanh_hotro as bh')
            ->leftJoin('nguoi_dung as nd',     'bh.ma_nguoidung', '=', 'nd.id_nguoidung')
            ->leftJoin('nhanvien as nv',        'bh.ma_nhanvien',  '=', 'nv.id_nhanvien')
            ->leftJoin('nguoi_dung as nd_nv',   'nv.id_nguoidung', '=', 'nd_nv.id_nguoidung')
            ->leftJoin('chi_nhanh as cn',        'bh.ma_chinhanh',  '=', 'cn.id_chinhanh')
            ->leftJoin('sanpham_serials as sp',  'bh.ma_serial',    '=', 'sp.id_serial')
            ->leftJoin('ton_kho_cuc_bo as tk',   'sp.ma_tonkho',    '=', 'tk.id_khoton')
            ->leftJoin('san_pham as s',           'tk.ma_sanpham',   '=', 's.id_sanpham')
            ->select(
                'bh.id',
                'bh.loai_yeu_cau',
                'bh.trang_thai',
                'bh.mo_ta_loi',
                'bh.ngay_tiep_nhan',
                'bh.ngay_hoan_thanh',
                'bh.ma_donhang',
                'nd.ten as ten_khachhang',
                'nd.sdt as sdt_khach',
                'nd_nv.ten as ten_nhanvien',
                'cn.ten_chinhanh',
                'sp.serial_code',
                's.tensp as ten_sanpham',
                's.thumbail as anh_sanpham'
            );
        $user = $request->user();
        if ((int) $user->phanquyen === 2) {
            $staff = $this->getStaffInfo($request);
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Bạn chưa được phân công chi nhánh.'], 403);
            }
            $query->where('bh.ma_chinhanh', $staff->machinhanh);
        }

        if ($request->filled('trang_thai') && $request->trang_thai !== 'all') {
            $query->where('bh.trang_thai', $request->trang_thai);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nd.ten', 'ilike', "%{$s}%")
                  ->orWhere('nd.sdt', 'ilike', "%{$s}%")
                  ->orWhere('sp.serial_code', 'ilike', "%{$s}%")
                  ->orWhere('cn.ten_chinhanh', 'ilike', "%{$s}%")
                  ->orWhere('bh.id', 'ilike', "%{$s}%"); // Mặc dù id là số nguyên, Laravel sẽ tự cast chuỗi
            });
        }

        $data = $query->orderBy('bh.ngay_tiep_nhan', 'desc')->paginate(15);

        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'ma_nguoidung' => 'required|integer|exists:nguoi_dung,id_nguoidung',
            'loai_yeu_cau' => 'required|in:Bảo hành,Hỗ trợ kỹ thuật,Đổi trả',
            'mo_ta_loi'    => 'required|string|max:2000',
            'ma_donhang'   => 'nullable|integer|exists:don_hang,id_donhang',
            'ma_serial'    => 'nullable|integer|exists:sanpham_serials,id_serial',
        ]);

        $staff = $this->getStaffInfo($request);
        if (!$staff) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy thông tin nhân viên.'], 403);
        }

        $phieu = BaoHanh_HoTro::create([
            'ma_donhang'    => $request->ma_donhang,
            'ma_nguoidung'  => $request->ma_nguoidung,
            'ma_nhanvien'   => $staff->id_nhanvien,
            'ma_chinhanh'   => $staff->machinhanh,
            'ma_serial'     => $request->ma_serial,
            'loai_yeu_cau'  => $request->loai_yeu_cau,
            'mo_ta_loi'     => $request->mo_ta_loi,
            'trang_thai'    => 'Đang xử lý',
            'ngay_tiep_nhan'=> now(),
        ]);

        // Thông báo cho Admin
        ThongBao::create([
            'loai_thong_bao' => 'WARRANTY',
            'tieu_de'        => 'Phiếu bảo hành mới',
            'noi_dung'       => "Nhân viên vừa tạo phiếu {$request->loai_yeu_cau} #" . $phieu->id,
            'link'           => '/admin/bao-hanh',
        ]);

        // Thông báo cho Khách hàng
        ThongBaoKhachHang::create([
            'id_nguoidung'   => $request->ma_nguoidung,
            'loai_thong_bao' => 'bao_hanh',
            'tieu_de'        => 'Phiếu bảo hành của bạn đã được tiếp nhận',
            'noi_dung'       => "Yêu cầu {$request->loai_yeu_cau} #" . $phieu->id . " đang được xử lý.",
            'link'           => '/tai-khoan/don-hang',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Tạo phiếu thành công!', 'data' => $phieu], 201);
    }

    // ── Chi tiết 1 phiếu ─────────────────────────────────────────────────────
    public function show(Request $request, int $id)
    {
        $phieu = DB::table('baohanh_hotro as bh')
            ->leftJoin('nguoi_dung as nd',     'bh.ma_nguoidung', '=', 'nd.id_nguoidung')
            ->leftJoin('nhanvien as nv',        'bh.ma_nhanvien',  '=', 'nv.id_nhanvien')
            ->leftJoin('nguoi_dung as nd_nv',   'nv.id_nguoidung', '=', 'nd_nv.id_nguoidung')
            ->leftJoin('chi_nhanh as cn',        'bh.ma_chinhanh',  '=', 'cn.id_chinhanh')
            ->leftJoin('sanpham_serials as sp',  'bh.ma_serial',    '=', 'sp.id_serial')
            ->leftJoin('ton_kho_cuc_bo as tk',   'sp.ma_tonkho',    '=', 'tk.id_khoton')
            ->leftJoin('san_pham as s',           'tk.ma_sanpham',   '=', 's.id_sanpham')
            ->leftJoin('don_hang as dh',          'bh.ma_donhang',   '=', 'dh.id_donhang')
            ->where('bh.id', $id)
            ->select(
                'bh.*',
                'nd.ten as ten_khachhang', 'nd.sdt as sdt_khach', 'nd.email as email_khach',
                'nd_nv.ten as ten_nhanvien',
                'cn.ten_chinhanh', 'cn.diachi_chitiet as diachi_chinhanh',
                'sp.serial_code', 'sp.tinhtrang as tinhtrang_serial',
                's.tensp as ten_sanpham', 's.thumbail as anh_sanpham',
                'dh.thoigiandathang', 'dh.tongtien as tongtien_donhang'
            )
            ->first();

        if (!$phieu) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu.'], 404);
        }

        // Staff chỉ được xem phiếu chi nhánh mình
        $user = $request->user();
        if ((int) $user->phanquyen === 2) {
            $staff = $this->getStaffInfo($request);
            if (!$staff || $phieu->ma_chinhanh != $staff->machinhanh) {
                return response()->json(['status' => 'error', 'message' => 'Không có quyền xem phiếu này.'], 403);
            }
        }

        return response()->json(['status' => 'success', 'data' => $phieu]);
    }

    // ── Cập nhật trạng thái ──────────────────────────────────────────────────
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'trang_thai'    => 'required|in:Chờ tiếp nhận,Đang xử lý,Hoàn thành,Từ chối',
            'ket_qua_xu_ly' => 'nullable|string|max:2000',
        ]);

        $phieu = BaoHanh_HoTro::find($id);
        if (!$phieu) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu.'], 404);
        }

        // Staff chỉ được thao tác phiếu chi nhánh mình
        $user = $request->user();
        if ((int) $user->phanquyen === 2) {
            $staff = $this->getStaffInfo($request);
            if (!$staff || $phieu->ma_chinhanh != $staff->machinhanh) {
                return response()->json(['status' => 'error', 'message' => 'Không có quyền.'], 403);
            }
        }

        $newState = $request->trang_thai;
        $phieu->trang_thai    = $newState;
        $phieu->ket_qua_xu_ly = $request->ket_qua_xu_ly;
        if (in_array($newState, ['Hoàn thành', 'Từ chối'])) {
            $phieu->ngay_hoan_thanh = now();
        }
        $phieu->save();

        // Thông báo cho Khách hàng
        ThongBaoKhachHang::create([
            'id_nguoidung'   => $phieu->ma_nguoidung,
            'loai_thong_bao' => 'bao_hanh',
            'tieu_de'        => 'Cập nhật phiếu bảo hành',
            'noi_dung'       => "Phiếu #{$id} đã được chuyển sang: {$newState}.",
            'link'           => '/tai-khoan/don-hang',
        ]);

        return response()->json(['status' => 'success', 'message' => "Đã cập nhật: {$newState}"]);
    }

    // ── Xóa phiếu (Admin only) ───────────────────────────────────────────────
    public function destroy(int $id)
    {
        $phieu = BaoHanh_HoTro::find($id);
        if (!$phieu) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu.'], 404);
        }
        $phieu->delete();
        return response()->json(['status' => 'success', 'message' => 'Đã xóa phiếu bảo hành.']);
    }

    // ── Tìm kiếm serial theo code (hỗ trợ form tạo phiếu) ───────────────────
    public function searchSerial(Request $request)
    {
        $request->validate(['serial_code' => 'required|string']);

        $serials = DB::table('sanpham_serials as sp')
            ->join('ton_kho_cuc_bo as tk', 'sp.ma_tonkho', '=', 'tk.id_khoton')
            ->join('san_pham as s', 'tk.ma_sanpham', '=', 's.id_sanpham')
            ->leftJoin('chi_tiet_don_hang__serial as cds', 'sp.id_serial', '=', 'cds.ma_serial')
            ->leftJoin('chi_tiet_don_hang as ctd', 'cds.ma_chitietdh', '=', 'ctd.id_chitietdh')
            ->leftJoin('don_hang as dh', 'ctd.ma_donhang', '=', 'dh.id_donhang')
            ->leftJoin('nguoi_dung as nd', 'dh.ma_nguoidung', '=', 'nd.id_nguoidung')
            ->where('sp.serial_code', 'like', '%' . $request->serial_code . '%')
            ->select(
                'sp.id_serial', 'sp.serial_code', 'sp.tinhtrang', 's.tensp', 's.thumbail',
                'dh.id_donhang', 'nd.id_nguoidung', 'nd.ten as ten_khachhang', 'nd.sdt as sdt_khach'
            )
            ->limit(10)
            ->get();

        return response()->json(['status' => 'success', 'data' => $serials]);
    }

    // ── Lấy đơn hàng theo khách hàng (hỗ trợ form tạo phiếu) ───────────────
    public function getOrdersByUser(Request $request)
    {
        $request->validate(['ma_nguoidung' => 'required|integer']);

        $orders = DB::table('don_hang')
            ->where('ma_nguoidung', $request->ma_nguoidung)
            ->whereIn('trang_thai_dh', ['Đã giao'])
            ->select('id_donhang', 'thoigiandathang', 'tongtien', 'trang_thai_dh')
            ->orderBy('thoigiandathang', 'desc')
            ->limit(20)
            ->get();

        return response()->json(['status' => 'success', 'data' => $orders]);
    }
}
