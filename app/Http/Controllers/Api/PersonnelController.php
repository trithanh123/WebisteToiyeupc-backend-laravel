<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\nhan_vien;
use App\Models\Nguoi_dung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\PersonnelstoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\ThongBao;
class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $personnel = nhan_vien::with(['chiNhanh', 'nguoiDung'])
                                ->orderBy('id_nhanvien', 'desc')
                                ->get();

        return response()->json([
            'status' => 'success',
            'total'  => $personnel->count(),
            'data'   => $personnel,
        ]);
    }

    public function store(PersonnelstoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $Usertontai = $request->filled('existing_user_id');
            if ($Usertontai) {
                $user = Nguoi_dung::find($request->existing_user_id);
                $user->phanquyen = 2; 
                $user->save();
            } else {
                $user = Nguoi_dung::create([
                    'ten'       => $request->ten,
                    'email'     => $request->email,
                    'matkhau'   => Hash::make($request->matkhau), 
                    'sdt'       => $request->sdt,
                    'phanquyen' => 2,
                ]);

                ThongBao::create([
                    'loai_thong_bao' => 'STAFF',
                    'tieu_de' => 'Nhân sự mới',
                    'noi_dung' => 'Nhân viên ' . $user->ten . ' vừa được thêm vào hệ thống.',
                    'link' => '/admin/nhan-su'
                ]);
            }
            if ($Usertontai) {
                 nhan_vien::where('id_nguoidung', $user->id_nguoidung)->delete();
            }

            $nhanVien = nhan_vien::create([
                'id_nguoidung' => $user->id_nguoidung,
                'chucvu'       => $request->chucvu,
                'machinhanh'   => $request->machinhanh,
            ]);

            DB::commit();

            $nhanVien->load(['chiNhanh', 'nguoiDung']);

            return response()->json([
                'status'  => 'success',
                'message' => $Usertontai ? 'Đã liên kết tài khoản thành Nhân viên!' : 'Thêm nhân viên thành công!',
                'data'    => $nhanVien
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi thêm nhân viên.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateStoreRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $nhanVien = nhan_vien::find($id);
            if (!$nhanVien) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy nhân viên'], 404);
            }
            $user = Nguoi_dung::find($nhanVien->id_nguoidung);
            if ($user) {
                if ($request->has('ten')) $user->ten = $request->ten;
                if ($request->has('email')) $user->email = $request->email;
                if ($request->has('sdt')) $user->sdt = $request->sdt;
                if ($request->filled('matkhau')) $user->matkhau = Hash::make($request->matkhau);
                $user->save();
            }

            if ($request->has('chucvu')) $nhanVien->chucvu = $request->chucvu;
            if ($request->has('machinhanh')) $nhanVien->machinhanh = $request->machinhanh;

            $nhanVien->save();
            DB::commit();

            $nhanVien->load(['chiNhanh', 'nguoiDung']);

            return response()->json(['status' => 'success', 'message' => 'Cập nhật thành công!', 'data' => $nhanVien], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi cập nhật.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $nhanVien = nhan_vien::find($id);
        if (!$nhanVien) return response()->json(['status' => 'error', 'message' => 'Không tìm thấy'], 404);
        $user = Nguoi_dung::find($nhanVien->id_nguoidung);
        if ($user) {
            $user->phanquyen = -1; 
            $user->save();
        }
        return response()->json(['status' => 'success', 'message' => 'Đã đánh dấu nghỉ việc thành công!'], 200);
    }
}
