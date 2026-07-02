<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use \App\Models\san_pham;
use \App\Models\danh_muc;
use \App\Models\Nguoi_dung;
use \App\Models\khuyen_mai;
use \App\Models\chi_nhanh;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }
    public function boot(): void
    {
        $models = [
            san_pham::class,
            danh_muc::class,
            Nguoi_dung::class,
            khuyen_mai::class,
            chi_nhanh::class,
        ];
        foreach ($models as $modelClass) {
            $modelClass::created(function ($item) use ($modelClass) {
                self::createNotification('Thêm mới dữ liệu', 'vừa thêm mới một bản ghi vào', $modelClass);
            });
            $modelClass::updated(function ($item) use ($modelClass) {
                self::createNotification('Cập nhật dữ liệu', 'vừa cập nhật một bản ghi trong', $modelClass);
            });
            $modelClass::deleted(function ($item) use ($modelClass) {
                self::createNotification('Xóa dữ liệu', 'vừa xóa một bản ghi khỏi', $modelClass);
            });
        }
        RateLimiter::for('login', function (Request $request) {
            $identifier = $request->input('email') ?? $request->input('identifier') ?? 'guest';
            return Limit::perMinute(5)
                ->by($request->ip() . '|' . $identifier)
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau 60 giây.',
                        'retry_after' => 60,
                    ], 429);
                });
        });
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(10, 3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Quá nhiều yêu cầu đăng ký. Vui lòng thử lại sau.',
                        'retry_after' => 600,
                    ], 429);
                });
        });
        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinutes(5, 3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Quá nhiều yêu cầu OTP. Vui lòng thử lại sau 5 phút.',
                        'retry_after' => 300,
                    ], 429);
                });
        });
    }
    private static function createNotification($title, $actionText, $modelClass)
    {
        $user = auth('sanctum')->user();
        $name = $user ? $user->Ten : 'Hệ thống';
        $modelName = class_basename($modelClass);
        \App\Models\ThongBao::create([
            'loai_thong_bao' => strtoupper($modelName),
            'tieu_de' => $title,
            'noi_dung' => "{$name} {$actionText} quản lý {$modelName}.",
            'da_doc' => false
        ]);
    }
}
