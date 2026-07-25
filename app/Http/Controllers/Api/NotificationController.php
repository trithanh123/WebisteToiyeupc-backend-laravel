<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ThongBao::orderBy('created_at', 'desc')
    ->limit(20)
    ->get()
    ->map(function ($n) {
        $n->da_doc = !empty($n->nguoi_doc);
        return $n;
    });
        $unreadCount = ThongBao::whereJsonLength('nguoi_doc', 0)->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'data' => $notifications
        ]);
    }
    public function markAsRead($id)
    {
        $notification = ThongBao::find($id);
        if ($notification) {
            $notification->update(['nguoi_doc' => [1]]);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error', 'message' => 'Không tìm thấy thông báo'], 404);
    }
    public function markAllAsRead()
    {
    ThongBao::whereJsonLength('nguoi_doc', 0)->update(['nguoi_doc' => json_encode([1])]);
    return response()->json(['status' => 'success']);
    }
    public function destroy($id)
    {
        ThongBao::destroy($id);
        return response()->json(['status' => 'success']);
    }
}
