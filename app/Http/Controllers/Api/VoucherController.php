<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\khuyen_mai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\updateVoucherRequest;
class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = khuyen_mai::orderBy('id_khuyenmai', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'total'  => $vouchers->count(),
            'data'   => $vouchers,
        ]);
    }
    public function store(StoreVoucherRequest $request)
    {
        $voucher = khuyen_mai::create($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Thêm mã khuyến mãi thành công!',
            'data'    => $voucher
        ], 201);
    }

    public function update(updateVoucherRequest $request, $id)
    {
        $voucher = khuyen_mai::find($id);
        if (!$voucher) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy voucher với ID = ' . $id
            ], 404);
        }
        $voucher->update($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Cập nhật mã khuyến mãi thành công!',
            'data'    => $voucher
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $voucher = khuyen_mai::find($id);
        if (!$voucher) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy voucher'
            ], 404);
        }

        $voucher->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa mã khuyến mãi thành công!'
        ], 200);
    }
    public function activeVouchers()
    {
        $now = now();
        $vouchers = khuyen_mai::where('ngaybdchuongtrinh', '<=', $now)
            ->where('ngayketthucchuongtrinh', '>=', $now)
            ->whereRaw('soluongma > dasudung')
            ->orderBy('id_khuyenmai', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $vouchers,
        ]);
    }
}
