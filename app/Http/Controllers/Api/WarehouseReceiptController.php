<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class WarehouseReceiptController extends Controller
{
  
    public function index(Request $request)
    {
        $query = DB::table('phieu_nhap_kho')
            ->join('nha_cung_cap', 'phieu_nhap_kho.ma_nhacungcap', '=', 'nha_cung_cap.id_nhacungcap')
            ->join('chi_nhanh', 'phieu_nhap_kho.ma_chinhanh', '=', 'chi_nhanh.id_chinhanh')
            ->select(
                'phieu_nhap_kho.id_phieunhap',
                'phieu_nhap_kho.ngaynhap',
                'phieu_nhap_kho.ghi_chu',
                'phieu_nhap_kho.trang_thai',
                'phieu_nhap_kho.created_at',
                'nha_cung_cap.ten_nhacungcap',
                'chi_nhanh.ten_chinhanh'
            );


        if ($request->has('trang_thai')) {
            $query->where('phieu_nhap_kho.trang_thai', $request->trang_thai);
        }
        if ($request->has('ma_chinhanh')) {
            $query->where('phieu_nhap_kho.ma_chinhanh', $request->ma_chinhanh);
        }

        $phieuNhap = $query->orderBy('phieu_nhap_kho.id_phieunhap', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $phieuNhap,
        ]);
    }

    
    public function show(int $id)
    {
        $phieu = DB::table('phieu_nhap_kho')
            ->join('nha_cung_cap', 'phieu_nhap_kho.ma_nhacungcap', '=', 'nha_cung_cap.id_nhacungcap')
            ->join('chi_nhanh', 'phieu_nhap_kho.ma_chinhanh', '=', 'chi_nhanh.id_chinhanh')
            ->select(
                'phieu_nhap_kho.*',
                'nha_cung_cap.ten_nhacungcap',
                'chi_nhanh.ten_chinhanh'
            )
            ->where('phieu_nhap_kho.id_phieunhap', $id)
            ->first();

        if (!$phieu) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy phiếu nhập kho.',
            ], 404);
        }

       
        $chiTiet = DB::table('chi_tiet_phieu_nhap')
            ->join('san_pham', 'chi_tiet_phieu_nhap.ma_sanpham', '=', 'san_pham.id_sanpham')
            ->select(
                'chi_tiet_phieu_nhap.*',
                'san_pham.tensp',
                'san_pham.thumbail'
            )
            ->where('chi_tiet_phieu_nhap.ma_phieunhap', $id)
            ->get();

        $serials = DB::table('sanpham_serials')
            ->where('ma_phieunhap', $id)
            ->select('id_serial', 'ma_sanpham', 'serial_code', 'tinhtrang', 'ngaycuthe')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'phieu'    => $phieu,
                'chi_tiet' => $chiTiet,
                'serials'  => $serials,
            ],
        ]);
    }

   
    public function store(Request $request)
    {
   
        $request->validate([
            'ma_nhacungcap'         => 'required|integer',
            'ngaynhap'              => 'required|date',
            'ma_chinhanh'           => 'required|integer',
            'ghi_chu'               => 'nullable|string|max:500',
            'san_pham'              => 'required|array|min:1',
            'san_pham.*.ma_sanpham' => 'required|integer',
            'san_pham.*.serials'    => 'required|array|min:1',
            'san_pham.*.serials.*'  => 'required|string|max:100',
            'san_pham.*.so_luong_nhap' => 'required|integer|min:1',
        ]);
        $ngayNhap = Carbon::parse($request->ngaynhap)->startOfDay();
        if ($ngayNhap->isFuture()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ngày nhập không hợp lệ: không được chọn ngày trong tương lai.',
                'fields'  => ['ngaynhap' => 'Ngày nhập phải là hôm nay hoặc trước đây.'],
            ], 422);
        }

        $nhaCungCapExists = DB::table('nha_cung_cap')
            ->where('id_nhacungcap', $request->ma_nhacungcap)
            ->exists();

        if (!$nhaCungCapExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nhà cung cấp không tồn tại trong hệ thống.',
                'fields'  => ['ma_nhacungcap' => 'Vui lòng chọn lại nhà cung cấp hợp lệ.'],
            ], 422);
        }
        $chiNhanhExists = DB::table('chi_nhanh')
            ->where('id_chinhanh', $request->ma_chinhanh)
            ->exists();

        if (!$chiNhanhExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Chi nhánh nhận hàng không tồn tại trong hệ thống.',
                'fields'  => ['ma_chinhanh' => 'Vui lòng chọn lại chi nhánh hợp lệ.'],
            ], 422);
        }
        $allSerialsInRequest = [];   
        foreach ($request->san_pham as $index => $item) {
            $serials      = $item['serials'];
            $soLuongNhap  = (int) $item['so_luong_nhap'];
            if ($soLuongNhap !== count($serials)) {
                $errors[] = [
                    'san_pham_index' => $index,
                    'ma_sanpham'     => $item['ma_sanpham'],
                    'loi'            => "Số lượng nhập ({$soLuongNhap}) không khớp với số Serial đã quét (" . count($serials) . ").",
                ];
            }
            foreach ($serials as $serial) {
                $serialUpper = strtoupper(trim($serial));

                if (in_array($serialUpper, $allSerialsInRequest)) {
                    $errors[] = [
                        'san_pham_index' => $index,
                        'serial'         => $serialUpper,
                        'loi'            => "Mã Serial [{$serialUpper}] bị quét trùng 2 lần trong cùng một phiếu nhập.",
                    ];
                } else {
                    $allSerialsInRequest[] = $serialUpper;
                }
                $existsInDB = DB::table('sanpham_serials')
                    ->where('serial_code', $serialUpper)
                    ->exists();

                if ($existsInDB) {
                    $errors[] = [
                        'san_pham_index' => $index,
                        'serial'         => $serialUpper,
                        'loi'            => "Mã Serial [{$serialUpper}] đã tồn tại trong hệ thống từ một lô hàng cũ trước đây.",
                    ];
                }
            }
        }
        if (!empty($errors)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Phát hiện lỗi dữ liệu Serial. Phiếu nhập bị chặn lưu.',
                'errors'  => $errors,
            ], 422);
        }
        try {
            DB::beginTransaction();
            $phieuNhapId = DB::table('phieu_nhap_kho')->insertGetId([
                'ma_nhacungcap' => $request->ma_nhacungcap,
                'ngaynhap'      => $request->ngaynhap,
                'ma_chinhanh'   => $request->ma_chinhanh,
                'ghi_chu'       => $request->ghi_chu,
                'trang_thai'    => 'Đã nhập kho',
                'created_at'    => now(),
                'updated_at'    => now(),
            ], 'id_phieunhap');

            foreach ($request->san_pham as $item) {
                $maSanPham   = $item['ma_sanpham'];
                $serials     = $item['serials'];
                $soLuongNhap = count($serials); 
                DB::table('chi_tiet_phieu_nhap')->insert([
                    'ma_phieunhap'  => $phieuNhapId,
                    'ma_sanpham'    => $maSanPham,
                    'so_luong_nhap' => $soLuongNhap,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                $tonKho = DB::table('ton_kho_cuc_bo')
                    ->where('ma_chinhanh', $request->ma_chinhanh)
                    ->where('ma_sanpham', $maSanPham)
                    ->first();

                $isFirstTimeImport = false;

                if ($tonKho) {
                    DB::table('ton_kho_cuc_bo')
                        ->where('id_khoton', $tonKho->id_khoton)
                        ->increment('soluongtonkho', $soLuongNhap);
                } else {
        
                    $isFirstTimeImport = true;
                    DB::table('ton_kho_cuc_bo')->insert([
                        'ma_chinhanh'    => $request->ma_chinhanh,
                        'ma_sanpham'     => $maSanPham,
                        'soluongtonkho'  => $soLuongNhap,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                
                $tonKhoRecord = DB::table('ton_kho_cuc_bo')
                    ->where('ma_chinhanh', $request->ma_chinhanh)
                    ->where('ma_sanpham', $maSanPham)
                    ->first();

                
                $serialRows = [];
                foreach ($serials as $serial) {
                    $serialRows[] = [
                        'ma_tonkho'    => $tonKhoRecord->id_khoton,
                        'ma_sanpham'   => $maSanPham,
                        'ma_phieunhap' => $phieuNhapId,
                        'serial_code'  => strtoupper(trim($serial)),
                        'tinhtrang'    => 'nằm trong kho',
                        'ngaycuthe'    => now(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
                DB::table('sanpham_serials')->insert($serialRows);

               
                if ($isFirstTimeImport) {
                    $mucCanhBao = max(1, (int) floor($soLuongNhap * 0.2)); 
                    DB::table('ton_kho_cuc_bo')
                        ->where('id_khoton', $tonKhoRecord->id_khoton)
                        ->update(['min_soluongkho' => $mucCanhBao]);
                }
            }

            DB::commit();

            return response()->json([
                'status'        => 'success',
                'message'       => 'Lưu phiếu nhập thành công! Tồn kho đã được cập nhật.',
                'id_phieunhap'  => $phieuNhapId,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi hệ thống khi lưu phiếu nhập: ' . $e->getMessage(),
            ], 500);
        }
    }

   
    public function destroy(int $id)
    {
        $phieu = DB::table('phieu_nhap_kho')->where('id_phieunhap', $id)->first();

        if (!$phieu) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy phiếu nhập kho.',
            ], 404);
        }

        
        $daCoSerial = DB::table('sanpham_serials')
            ->where('ma_phieunhap', $id)
            ->where('tinhtrang', '!=', 'nằm trong kho')
            ->exists();

        if ($daCoSerial) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không thể xóa phiếu nhập vì đã có Serial được xuất kho/bán ra.',
            ], 409);
        }

        try {
            DB::beginTransaction();

            
            $chiTiet = DB::table('chi_tiet_phieu_nhap')
                ->where('ma_phieunhap', $id)
                ->get();

            foreach ($chiTiet as $item) {
                DB::table('ton_kho_cuc_bo')
                    ->where('ma_chinhanh', $phieu->ma_chinhanh)
                    ->where('ma_sanpham', $item->ma_sanpham)
                    ->decrement('soluongtonkho', $item->so_luong_nhap);
            }
            DB::table('sanpham_serials')->where('ma_phieunhap', $id)->delete();
            DB::table('chi_tiet_phieu_nhap')->where('ma_phieunhap', $id)->delete();
            DB::table('phieu_nhap_kho')->where('id_phieunhap', $id)->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Đã xóa phiếu nhập kho và hoàn lại tồn kho thành công.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi khi xóa phiếu nhập: ' . $e->getMessage(),
            ], 500);
        }
    }
}


