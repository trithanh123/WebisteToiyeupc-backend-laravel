<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ThongBaoKhachHang;

class ClientNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = ThongBaoKhachHang::where('id_nguoidung', $user->id_nguoidung)
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = ThongBaoKhachHang::where('id_nguoidung', $user->id_nguoidung)
            ->where('da_doc', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = ThongBaoKhachHang::where('id_thongbao', $id)
            ->where('id_nguoidung', $user->id_nguoidung)
            ->first();

        if ($notification) {
            $notification->update(['da_doc' => true]);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error', 'message' => 'Không tìm thấy thông báo'], 404);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        ThongBaoKhachHang::where('id_nguoidung', $user->id_nguoidung)
            ->where('da_doc', false)
            ->update(['da_doc' => true]);

        return response()->json(['status' => 'success']);
    }
}
