<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ton_kho_cuc_bo;
use App\Models\sanpham_serials;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
class WarehouseController extends Controller
{
    public function store(StoreWarehouseRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
        DB::beginTransaction(); 
        $phieuNhapId = time(); 
        foreach ($validated['san_phams'] as $item) {
            $maSanPham  = $item['ma_san_pham'];
            $serials    = $item['serials'] ?? []; 
            $soLuongMoi = $item['soluongtonkho']; 
            $dinhMuc    = $item['soluongkhothap'];
            $tonKhoHienTai = ton_kho_cuc_bo::where('ma_sanpham', $maSanPham)
                ->where('ma_chinhanh', $validated['ma_chi_nhanh'])
                ->first();
            $tonKho = ton_kho_cuc_bo::updateOrCreate(
                [
                    'ma_sanpham'  => $maSanPham,
                    'ma_chinhanh' => $validated['ma_chi_nhanh']
                ],
                [
                    'soluongtonkho'  => ($tonKhoHienTai ? $tonKhoHienTai->soluongtonkho : 0) + $soLuongMoi,
                    'soluongkhothap' => $dinhMuc, 
                ]
            );
            $serialDataToInsert = [];
            foreach ($serials as $chuoiSerial) {
                $serialDataToInsert[] = [
                    'ma_tonkho'       => $tonKho->id_khoton,  
                    'serial_code'    => $chuoiSerial,
                    'tinhtrang'      => 'nằm trong kho',    
                    'Min_soluongkho' => $dinhMuc,      
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            if (!empty($serialDataToInsert)) {
                sanpham_serials::insert($serialDataToInsert);
            }
        }
        DB::commit();
        return response()->json([
            'status'  => 'success',
            'message' => 'Lưu phiếu nhập, cập nhật tồn kho và lưu serial thành công!',
            'data'    => [
                'phieu_nhap_id' => $phieuNhapId
            ]
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 'error',
            'message' => 'Có lỗi xảy ra khi lưu phiếu nhập.',
            'error'   => $e->getMessage()
        ], 500);
    }
    }
    public function index(Request $request)
    {
        $khoHang = ton_kho_cuc_bo::with(['sanPham', 'chiNhanh'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $khoHang
        ]);
    }
    public function getSerials($id)
    {
        $serials = sanpham_serials::where('ma_tonkho', $id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $serials
        ]);
    }
    public function update(UpdateWarehouseRequest $request, $id)
    {
        $tonKho = ton_kho_cuc_bo::find($id);
        if (!$tonKho) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy lô hàng'], 404);
        }
        if (isset($validated['soluongtonkho'])) {
            $tonKho->soluongtonkho = $validated['soluongtonkho'];
        }
        if (isset($validated['soluongkhothap'])) {
            $tonKho->soluongkhothap = $validated['soluongkhothap'];
        }
        $tonKho->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thành công',
            'data' => $tonKho
        ]);
    }
    public function destroy($id)
    {
        try {
            $tonKho = ton_kho_cuc_bo::find($id);
            if (!$tonKho) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy mục tồn kho'], 404);
            }
            $tonKho->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa sản phẩm khỏi kho hàng'
            ]);
        } catch (\Exception $e) {
            \Log::error('Warehouse delete error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
}
