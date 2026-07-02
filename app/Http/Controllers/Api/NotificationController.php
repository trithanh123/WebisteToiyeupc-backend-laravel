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
            ->get();
        $unreadCount = ThongBao::where('da_doc', false)->count();
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
            $notification->update(['da_doc' => true]);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error', 'message' => 'Không tìm thấy thông báo'], 404);
    }
    public function markAllAsRead()
    {
        ThongBao::where('da_doc', false)->update(['da_doc' => true]);
        return response()->json(['status' => 'success']);
    }
    public function destroy($id)
    {
        ThongBao::destroy($id);
        return response()->json(['status' => 'success']);
    }
}
