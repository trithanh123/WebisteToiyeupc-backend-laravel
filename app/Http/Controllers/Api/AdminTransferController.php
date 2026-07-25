<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\phieu_dieu_chuyen;
use App\Models\chi_tiet_dieu_chuyen;
use App\Models\dieu_chuyen_serials;
use App\Models\ton_kho_cuc_bo;
use App\Models\sanpham_serials;
use App\Models\san_pham;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = phieu_dieu_chuyen::with(['khoXuat', 'khoNhap', 'nguoiTao', 'nguoiDuyet']);
        
        if ($request->has('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $transfers
        ]);
    }

    public function getAvailableSerials(Request $request)
    {
        $ma_sanpham = $request->ma_sanpham;
        $ma_chinhanh = $request->ma_chinhanh;
        
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ma_kho_xuat' => 'required|exists:chi_nhanh,id_chinhanh',
            'ma_kho_nhap' => 'required|exists:chi_nhanh,id_chinhanh|different:ma_kho_xuat',
            'chi_tiet' => 'required|array|min:1',
            'chi_tiet.*.ma_sanpham' => 'required|exists:san_pham,id_sanpham',
            'chi_tiet.*.so_luong' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
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
                $sanPham = san_pham::find($item['ma_sanpham']);
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
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi tạo phiếu: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $phieu = phieu_dieu_chuyen::with([
            'khoXuat', 'khoNhap', 'nguoiTao', 'nguoiDuyet',
            'chiTiet.sanPham',
            'chiTiet.serials.serial'
        ])->find($id);

        if (!$phieu) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $phieu]);
    }

    public function approve(Request $request, $id)
    {
        // Duyệt phiếu & Chọn serial
        $phieu = phieu_dieu_chuyen::find($id);
        if (!$phieu || $phieu->trang_thai !== 'Chờ duyệt') {
            return response()->json(['status' => 'error', 'message' => 'Phiếu không hợp lệ hoặc đã được xử lý'], 400);
        }

        $validator = Validator::make($request->all(), [
            'serials' => 'required|array', // key: id_chitiet, value: array of id_serial
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
                    // Kiểm tra serial có hợp lệ và đang nằm ở kho xuất không
                    $serialObj = sanpham_serials::find($id_serial);
                    if (!$serialObj || $serialObj->tinhtrang !== 'nằm trong kho') {
                        throw new \Exception("Serial $id_serial không hợp lệ hoặc không có sẵn");
                    }
                    
                    // Thêm vào bảng dieu_chuyen_serials
                    dieu_chuyen_serials::create([
                        'ma_chitiet' => $ct->id_chitiet,
                        'ma_serial' => $id_serial
                    ]);
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
        $phieu = phieu_dieu_chuyen::with('chiTiet.serials')->find($id);
        if (!$phieu || $phieu->trang_thai !== 'Đang vận chuyển') {
            return response()->json(['status' => 'error', 'message' => 'Phiếu không hợp lệ hoặc chưa được vận chuyển'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($phieu->chiTiet as $ct) {
                // Giảm tồn kho ở kho xuất
                $tonKhoXuat = ton_kho_cuc_bo::where('ma_chinhanh', $phieu->ma_kho_xuat)
                                            ->where('ma_sanpham', $ct->ma_sanpham)->first();
                if ($tonKhoXuat) {
                    $tonKhoXuat->decrement('soluongtonkho', $ct->so_luong);
                }

                // Tăng tồn kho ở kho nhập (tạo mới nếu chưa có)
                $tonKhoNhap = ton_kho_cuc_bo::firstOrCreate(
                    ['ma_chinhanh' => $phieu->ma_kho_nhap, 'ma_sanpham' => $ct->ma_sanpham],
                    ['soluongtonkho' => 0, 'soluongkhothap' => 5]
                );
                $tonKhoNhap->increment('soluongtonkho', $ct->so_luong);

                // Đổi ma_tonkho của từng serial sang kho nhập
                foreach ($ct->serials as $ds) {
                    $serialObj = sanpham_serials::find($ds->ma_serial);
                    if ($serialObj) {
                        $serialObj->update(['ma_tonkho' => $tonKhoNhap->id_khoton]);
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

    /**
     * Xóa phiếu điều chuyển - chỉ được xóa khi đang "Chờ duyệt"
     */
    public function destroy($id)
    {
        $phieu = phieu_dieu_chuyen::find($id);
        if (!$phieu) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu'], 404);
        }

        if ($phieu->trang_thai !== 'Chờ duyệt') {
            return response()->json([
                'status' => 'error',
                'message' => "Không thể xóa phiếu đang ở trạng thái \"{$phieu->trang_thai}\". Chỉ được xóa phiếu đang \"Chờ duyệt\"."
            ], 400);
        }

        DB::beginTransaction();
        try {
            chi_tiet_dieu_chuyen::where('ma_phieu', $id)->delete();
            $phieu->delete();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Đã xóa phiếu điều chuyển thành công.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi xóa: ' . $e->getMessage()], 500);
        }
    }
}
