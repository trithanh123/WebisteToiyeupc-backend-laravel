<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Nguoi_dung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;   
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ThongBao;
use Illuminate\Support\Facades\Mail;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = Nguoi_dung::select(['id_nguoidung','ten','email','sdt','phanquyen','mancc','avatar','created_at', ])
            ->orderBy('id_nguoidung', 'asc')
            ->get()
            ->map(function ($user) {
                $user->role_label = match ((int) $user->phanquyen) {
                    1 => 'Admin',
                    2 => 'Nhân viên',
                    3 => 'Khách hàng',
                    default => 'Không xác định',
                };
                return $user;
            });
        return response()->json([
            'status'  => 'success',
            'total'   => $users->count(),
            'data'    => $users,
        ], 200);
    }
    public function store(StoreUserRequest $request)
    {
        $user = Nguoi_dung::create([
            'ten'       => $request->ten,
            'email'     => $request->email,
            'sdt'       => $request->sdt,
            'matkhau'   => Hash::make($request->matkhau),
            'phanquyen' => $request->phanquyen ?? 3, 
        ]);
        ThongBao::create([
            'loai_thong_bao' => 'USER',
            'tieu_de' => 'Tài khoản mới',
            'noi_dung' => 'Admin vừa tạo thành công tài khoản cho ' . $user->ten . '.',
            'link' => '/admin/nguoi-dung'
        ]);
        return response()->json([
            'status'  => 'success',
            'message' => 'Tạo tài khoản mới thành công!',
            'data'    => [
                'id_nguoidung' => $user->id_nguoidung,
                'ten'          => $user->ten,
                'email'        => $user->email,
                'sdt'          => $user->sdt,
                'phanquyen'    => $user->phanquyen,
            ],
        ], 201);
    }
    public function show(Request $request, $id)
    {
        $user = Nguoi_dung::select([
                'id_nguoidung', 'ten', 'email', 'sdt',
                'phanquyen', 'mancc', 'avatar', 'created_at',
            ])->find($id);
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy người dùng với ID = ' . $id,
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data'   => $user,
        ], 200);
    }
    public function update(UpdateUserRequest $request, $id)
    {
        $user = Nguoi_dung::find($id);
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy người dùng với ID = ' . $id,
            ], 404);
        }
         $user->update($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Cập nhật thông tin người dùng thành công!',
            'data'    => [
                'id_nguoidung' => $user->id_nguoidung,
                'ten'          => $user->ten,
                'email'        => $user->email,
                'sdt'          => $user->sdt,
                'phanquyen'    => $user->phanquyen,
            ],
        ], 200);
    }
    public function destroy(Request $request, $id)
    {
        $user = Nguoi_dung::find($id);
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy người dùng với ID = ' . $id,
            ], 404);
        }
        if ($user->id_nguoidung === $request->user()->id_nguoidung) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bạn không thể tự xóa tài khoản của chính mình!',
            ], 403);
        }
        $user->tokens()->delete();
        $user->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa tài khoản thành công!',
        ], 200);
    }
}
