<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\nhan_vien;
use App\Models\chi_nhanh;
use App\Models\phieu_dieu_chuyen;
use App\Models\chi_tiet_dieu_chuyen;
use App\Models\dieu_chuyen_serials;
use App\Models\ton_kho_cuc_bo;
use App\Models\sanpham_serials;
use App\Http\Requests\StoreStaffTransferRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class StaffTransferController extends Controller
{
    private function getStaffBranch(Request $request)
    {
        $user = $request->user();
        $nhanVien = nhan_vien::where('id_nguoidung', $user->id_nguoidung)->first();
        if (!$nhanVien || !$nhanVien->machinhanh) {
            return null;
        }

        return chi_nhanh::where('id_chinhanh', $nhanVien->machinhanh)->first();
    }

    public function index(Request $request)
    {
        $chiNhanh = $this->getStaffBranch($request);
        if (!$chiNhanh) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chưa được phân công chi nhánh'], 403);
        }

        $query = phieu_dieu_chuyen::with(['khoXuat', 'khoNhap', 'nguoiTao', 'nguoiDuyet'])
            ->where(function($q) use ($chiNhanh) {
                $q->where('ma_kho_xuat', $chiNhanh->id_chinhanh)
                  ->orWhere('ma_kho_nhap', $chiNhanh->id_chinhanh);
            });
        
        if ($request->has('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'branch' => $chiNhanh,
            'data' => $transfers
        ]);
    }

    public function getAvailableSerials(Request $request)
    {
        $chiNhanh = $this->getStaffBranch($request);
        if (!$chiNhanh) return response()->json(['status' => 'error', 'message' => 'Không có chi nhánh'], 403);

        $ma_sanpham = $request->ma_sanpham;
        $ma_chinhanh = $request->ma_chinhanh;
        
        if ($chiNhanh->id_chinhanh != $ma_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Từ chối truy cập. Chỉ xem được tồn kho chi nhánh bạn.'], 403);
        }

        if (!$ma_sanpham || !$ma_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Thiếu tham số'], 400);
        }

        $tonKho = ton_kho_cuc_bo::where('ma_sanpham', $ma_sanpham)
                                ->where('ma_chinhanh', $ma_chinhanh)->first();
                                
        if (!$tonKho) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $serials = sanpham_serials::where('ma_tonkho', $tonKho->id_khoton)
                                  ->where('tinhtrang', 'nằm trong kho')->get();

        return response()->json(['status' => 'success', 'data' => $serials]);
    }

    public function store(StoreStaffTransferRequest $request)
    {
        $chiNhanh = $this->getStaffBranch($request);
        if (!$chiNhanh) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chưa được phân công chi nhánh'], 403);
        }

        if ($request->ma_kho_xuat != $chiNhanh->id_chinhanh && $request->ma_kho_nhap != $chiNhanh->id_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chỉ được tạo phiếu liên quan đến chi nhánh của mình'], 403);
        }
        foreach ($request->chi_tiet as $item) {
            $tonKho = ton_kho_cuc_bo::where('ma_chinhanh', $request->ma_kho_xuat)
                ->where('ma_sanpham', $item['ma_sanpham'])
                ->first();
            
            $availableSerialsCount = 0;
            if ($tonKho) {
                $availableSerialsCount = sanpham_serials::where('ma_tonkho', $tonKho->id_khoton)
                                          ->where('tinhtrang', 'nằm trong kho')
                                          ->count();
            }
            
            if (!$tonKho || $tonKho->soluongtonkho < $item['so_luong'] || $availableSerialsCount < $item['so_luong']) {
                $sanPham = \App\Models\san_pham::find($item['ma_sanpham']);
                $tenSP = $sanPham ? $sanPham->ten_sanpham : 'Mã ' . $item['ma_sanpham'];
                $actualStock = $tonKho ? min($tonKho->soluongtonkho, $availableSerialsCount) : 0;
                return response()->json([
                    'status' => 'error', 
                    'message' => "Sản phẩm '{$tenSP}' không đủ tồn kho tại chi nhánh xuất (Chỉ còn {$actualStock} serial hợp lệ)."
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $phieu = phieu_dieu_chuyen::create([
                'ma_kho_xuat' => $request->ma_kho_xuat,
                'ma_kho_nhap' => $request->ma_kho_nhap,
                'nguoi_tao' => $request->user()->id_nguoidung,
                'trang_thai' => 'Chờ duyệt',
                'ly_do' => $request->ly_do,
                'ghi_chu' => $request->ghi_chu,
            ]);

            foreach ($request->chi_tiet as $item) {
                chi_tiet_dieu_chuyen::create([
                    'ma_phieu' => $phieu->id_phieu,
                    'ma_sanpham' => $item['ma_sanpham'],
                    'so_luong' => $item['so_luong']
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Tạo phiếu điều chuyển thành công', 'data' => $phieu], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $chiNhanh = $this->getStaffBranch($request);
        $phieu = phieu_dieu_chuyen::find($id);

        if (!$phieu || $phieu->trang_thai !== 'Chờ duyệt') {
            return response()->json(['status' => 'error', 'message' => 'Phiếu không hợp lệ hoặc đã được xử lý'], 400);
        }

        if (!$chiNhanh || $phieu->ma_kho_nhap != $chiNhanh->id_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Chỉ chi nhánh nhận (người tạo) mới có quyền hủy phiếu này'], 403);
        }

        $phieu->update([
            'trang_thai' => 'Đã hủy',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Đã hủy phiếu yêu cầu điều chuyển']);
    }

    public function reject(Request $request, $id)
    {
        $chiNhanh = $this->getStaffBranch($request);
        $phieu = phieu_dieu_chuyen::find($id);

        if (!$phieu || $phieu->trang_thai !== 'Chờ duyệt') {
            return response()->json(['status' => 'error', 'message' => 'Phiếu không hợp lệ hoặc đã được xử lý'], 400);
        }

        if (!$chiNhanh || $phieu->ma_kho_xuat != $chiNhanh->id_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Chỉ kho xuất (nguồn) mới có quyền từ chối phiếu này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'ly_do' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng nhập lý do từ chối (tối đa 500 ký tự)'], 400);
        }

        $phieu->update([
            'trang_thai' => 'Từ chối',
            'ly_do' => $request->ly_do,
            'nguoi_duyet' => $request->user()->id_nguoidung
        ]);

        return response()->json(['status' => 'success', 'message' => 'Đã từ chối phiếu yêu cầu điều chuyển']);
    }

    public function show(Request $request, $id)
    {
        $chiNhanh = $this->getStaffBranch($request);
        
        $phieu = phieu_dieu_chuyen::with([
            'khoXuat', 'khoNhap', 'nguoiTao', 'nguoiDuyet',
            'chiTiet.sanPham',
            'chiTiet.serials.serial'
        ])->find($id);

        if (!$phieu) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu'], 404);
        }

        if ($chiNhanh && $phieu->ma_kho_xuat != $chiNhanh->id_chinhanh && $phieu->ma_kho_nhap != $chiNhanh->id_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền truy cập phiếu này'], 403);
        }

        return response()->json([
            'status' => 'success', 
            'branch' => $chiNhanh,
            'data' => $phieu
        ]);
    }

    public function approve(Request $request, $id)
    {
        $chiNhanh = $this->getStaffBranch($request);
        $phieu = phieu_dieu_chuyen::find($id);

        if (!$phieu || $phieu->trang_thai !== 'Chờ duyệt') {
            return response()->json(['status' => 'error', 'message' => 'Phiếu không hợp lệ hoặc đã được xử lý'], 400);
        }

        
        if (!$chiNhanh || $phieu->ma_kho_xuat != $chiNhanh->id_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Từ chối truy cập. Chỉ kho xuất mới có quyền duyệt xuất hàng.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'serials' => 'required|array', 
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
        }

        DB::beginTransaction();
        try {
            $chiTiets = chi_tiet_dieu_chuyen::where('ma_phieu', $id)->get();
            $serialsData = $request->serials;

            foreach ($chiTiets as $ct) {
                $selectedSerials = $serialsData[$ct->id_chitiet] ?? [];
                if (count($selectedSerials) !== $ct->so_luong) {
                    throw new \Exception("Số lượng serial không khớp cho sản phẩm ID " . $ct->ma_sanpham);
                }

                foreach ($selectedSerials as $id_serial) {
                    $serialObj = sanpham_serials::find($id_serial);
                    if (!$serialObj || $serialObj->tinhtrang !== 'nằm trong kho') {
                        throw new \Exception("Serial $id_serial không hợp lệ hoặc không có sẵn");
                    }
                
                    $tonKhoXuat = ton_kho_cuc_bo::where('ma_chinhanh', $phieu->ma_kho_xuat)->where('ma_sanpham', $ct->ma_sanpham)->first();
                    if (!$tonKhoXuat || $serialObj->ma_tonkho != $tonKhoXuat->id_khoton) {
                        throw new \Exception("Serial $id_serial không nằm trong kho xuất");
                    }
                    
                    dieu_chuyen_serials::create([
                        'ma_chitiet' => $ct->id_chitiet,
                        'ma_serial' => $id_serial
                    ]);
                    $serialObj->update(['tinhtrang' => 'trong quá trình đổi trả/luân chuyển']);
                }
            }

            $phieu->update([
                'trang_thai' => 'Đang vận chuyển',
                'nguoi_duyet' => $request->user()->id_nguoidung
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Duyệt phiếu và xuất hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function complete(Request $request, $id)
    {
        $chiNhanh = $this->getStaffBranch($request);
        $phieu = phieu_dieu_chuyen::with('chiTiet.serials')->find($id);

        if (!$phieu || $phieu->trang_thai !== 'Đang vận chuyển') {
            return response()->json(['status' => 'error', 'message' => 'Phiếu không hợp lệ hoặc chưa được vận chuyển'], 400);
        }

        if (!$chiNhanh || $phieu->ma_kho_nhap != $chiNhanh->id_chinhanh) {
            return response()->json(['status' => 'error', 'message' => 'Từ chối truy cập. Chỉ kho nhận mới có quyền xác nhận nhận hàng.'], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($phieu->chiTiet as $ct) {
                $tonKhoXuat = ton_kho_cuc_bo::where('ma_chinhanh', $phieu->ma_kho_xuat)
                                            ->where('ma_sanpham', $ct->ma_sanpham)->first();
                if ($tonKhoXuat) {
                    $tonKhoXuat->decrement('soluongtonkho', $ct->so_luong);
                }
                $tonKhoNhap = ton_kho_cuc_bo::firstOrCreate(
                    ['ma_chinhanh' => $phieu->ma_kho_nhap, 'ma_sanpham' => $ct->ma_sanpham],
                    ['soluongtonkho' => 0, 'soluongkhothap' => 5]
                );
                $tonKhoNhap->increment('soluongtonkho', $ct->so_luong);
                foreach ($ct->serials as $ds) {
                    $serialObj = sanpham_serials::find($ds->ma_serial);
                    if ($serialObj) {
                        $serialObj->update([
                            'ma_tonkho' => $tonKhoNhap->id_khoton,
                            'tinhtrang' => 'nằm trong kho'
                        ]);
                    }
                }
            }

            $phieu->update(['trang_thai' => 'Hoàn thành']);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Xác nhận nhận hàng thành công. Tồn kho đã được cập nhật.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi hoàn thành phiếu: ' . $e->getMessage()], 500);
        }
    }
}
