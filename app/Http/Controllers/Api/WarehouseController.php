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
        DB::beginTransaction(); // Chỉ gọi 1 lần (đã bỏ dòng kép)
        try {
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
                        'ma_tonkho'      => $tonKho->id_khoton,
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
                'data'    => ['phieu_nhap_id' => $phieuNhapId]
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

    /**
     * Chỉ cho phép sửa định mức cảnh báo (soluongkhothap).
     * Không cho sửa số tồn kho thủ công vì sẽ gây mất đồng bộ với serial thực tế.
     */
    public function update(UpdateWarehouseRequest $request, $id)
    {
        $tonKho = ton_kho_cuc_bo::find($id);
        if (!$tonKho) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy lô hàng'], 404);
        }

        $validated = $request->validated(); // Đã fix: khai báo $validated đúng chỗ

        // Chỉ cho phép cập nhật định mức cảnh báo
        if (isset($validated['soluongkhothap'])) {
            $tonKho->soluongkhothap = $validated['soluongkhothap'];
        }
        $tonKho->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật định mức cảnh báo thành công',
            'data' => $tonKho
        ]);
    }

    /**
     * Xóa an toàn: chỉ cho xóa khi không còn serial nào đang trong kho.
     */
    public function destroy($id)
    {
        try {
            $tonKho = ton_kho_cuc_bo::find($id);
            if (!$tonKho) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy mục tồn kho'], 404);
            }

            // Chỉ cho xóa khi không còn serial trong kho
            $serialCount = sanpham_serials::where('ma_tonkho', $id)
                ->where('tinhtrang', 'nằm trong kho')
                ->count();

            if ($serialCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Không thể xóa! Kho này vẫn còn {$serialCount} serial đang tồn. Hãy xuất hết hàng trước."
                ], 400);
            }

            $tonKho->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa sản phẩm khỏi kho hàng'
            ]);
        } catch (\Exception $e) {
            \Log::error('Warehouse delete error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
}
