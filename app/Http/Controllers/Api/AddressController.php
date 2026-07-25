<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\diachi_nguoidung;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;

class AddressController extends Controller
{
    /**
     * API Lấy danh sách địa chỉ
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
            ->orderBy('matudien_diachi', 'desc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $addresses
        ]);
    }

    /**
     * API Thêm địa chỉ mới (Dành cho Khách hàng)
     */
    public function store(StoreAddressRequest $request)
    {
        $user = $request->user();

        // 1. Kiểm tra nếu là địa chỉ đầu tiên thì tự động ép thành Mặc định
        $addressCount = diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)->count();
        $isDefault = $request->input('matudien_diachi', false);
        
        if ($addressCount === 0) {
            $isDefault = true;
        }

        // 2. Nếu người dùng chọn địa chỉ này làm mặc định, phải gỡ mặc định của các địa chỉ cũ
        if ($isDefault) {
            diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
                ->update(['matudien_diachi' => false]);
        }

        // 3. Thêm mới vào Database
        $address = diachi_nguoidung::create([
            'id_nguoidung'    => $user->id_nguoidung,
            'ten_nguoinhan'   => $request->ten_nguoinhan,
            'sdt_nguoinhan'   => $request->sdt_nguoinhan,
            'ma_thanhpho'     => $request->ma_thanhpho,
            'ma_quan'         => $request->ma_quan,
            'ma_phuong'       => $request->ma_phuong,
            'diachi_chitiet'  => $request->diachi_chitiet,
            'matudien_diachi' => $isDefault,
        ]);

        // 4. Trả kết quả về cho Frontend
        return response()->json([
            'status'  => 'success',
            'message' => 'Thêm địa chỉ mới thành công!',
            'data'    => $address
        ], 201);
    }

    /**
     * API Cập nhật địa chỉ
     */
    public function update(UpdateAddressRequest $request, $id)
    {
        $user = $request->user();
        $address = diachi_nguoidung::where('id_diachinguoidung', $id)
                    ->where('id_nguoidung', $user->id_nguoidung)
                    ->first();
        if (!$address) {
            return response()->json(['message' => 'Không tìm thấy địa chỉ'], 404);
        }

        $isDefault = $request->input('matudien_diachi', false);
        if ($isDefault) {
            diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
                ->where('id_diachinguoidung', '!=', $id)
                ->update(['matudien_diachi' => false]);
        }

        $address->update([
            'ten_nguoinhan'   => $request->ten_nguoinhan,
            'sdt_nguoinhan'   => $request->sdt_nguoinhan,
            'ma_thanhpho'     => $request->ma_thanhpho,
            'ma_quan'         => $request->ma_quan,
            'ma_phuong'       => $request->ma_phuong,
            'diachi_chitiet'  => $request->diachi_chitiet,
            'matudien_diachi' => $isDefault,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật địa chỉ thành công!',
            'data' => $address
        ]);
    }

    /**
     * API Xóa địa chỉ
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $address = diachi_nguoidung::where('id_diachinguoidung', $id)
                    ->where('id_nguoidung', $user->id_nguoidung)
                    ->first();
        if (!$address) {
            return response()->json(['message' => 'Không tìm thấy địa chỉ'], 404);
        }

        if ($address->matudien_diachi) {
            return response()->json(['message' => 'Không thể xóa địa chỉ mặc định'], 422);
        }

        $address->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa địa chỉ thành công!'
        ]);
    }

    /**
     * API Thiết lập địa chỉ mặc định
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();
        $address = diachi_nguoidung::where('id_diachinguoidung', $id)
                    ->where('id_nguoidung', $user->id_nguoidung)
                    ->first();
        if (!$address) {
            return response()->json(['message' => 'Không tìm thấy địa chỉ'], 404);
        }

        diachi_nguoidung::where('id_nguoidung', $user->id_nguoidung)
            ->update(['matudien_diachi' => false]);

        $address->update(['matudien_diachi' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật địa chỉ mặc định thành công!'
        ]);
    }
}
