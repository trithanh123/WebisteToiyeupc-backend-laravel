<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class CheckadminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bạn chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.',
            ], 401);
        }
        if ((int) $user->phanquyen !== 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bạn không có quyền truy cập chức năng này.',
            ], 403);
        }
        return $next($request);
    }
}
