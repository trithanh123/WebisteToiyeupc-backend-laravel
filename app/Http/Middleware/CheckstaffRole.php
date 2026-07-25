<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckstaffRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bạn chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.',
            ], 401);
        }

        // Kiểm tra quyền Nhân viên (phanquyen = 2)
        if ((int) $user->phanquyen !== 2) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bạn không có quyền truy cập chức năng này. Yêu cầu quyền Nhân viên.',
            ], 403);
        }

        return $next($request);
    }
}
