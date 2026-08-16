<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginAuthRequest;
use App\Models\Nguoi_dung;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Mail;

use App\Mail\OtpMail;
class AuthController extends Controller
{
    
    public function login(LoginAuthRequest $request)
    {
        $identifier = trim($request->email);
        $user = Nguoi_dung::where('email', $identifier)
                          ->orWhere('sdt', $identifier)
                          ->first();
        if (!$user || !Hash::check($request->password, $user->matkhau)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email/Số điện thoại hoặc mật khẩu không chính xác!'
            ], 401);
        }
        $token = $user->createToken('ToiYeuPCToken')->plainTextToken;
        return response()->json([
            'status'  => 'success',
            'message' => 'Đăng nhập thành công!',
            'data'    => $user,
            'token'   => $token
        ], 200);
    }
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công!'
        ]);
    }

   
    public function redirectToProvider($provider)
    {
        try {
            return Socialite::driver($provider)->withPkce()->redirect();
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->withPkce()->user();
            $user = Nguoi_dung::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'ten'               => $socialUser->getName(),
                    'matkhau'           => Hash::make(Str::random(24)),
                    'mancc'             => 'google',
                    'mancc_id'          => $socialUser->getId(),
                    'avatar'            => $socialUser->getAvatar(),
                    'phanquyen'         => 3,
                    'email_verified_at' => now(),
                ]
            );
            $token = $user->createToken('auth_token')->plainTextToken;
            $exchangeCode = Str::random(40);
            Cache::put('oauth_code_' . $exchangeCode, $token, now()->addSeconds(60));
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/oauth/callback?code=' . $exchangeCode);
        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/oauth/callback?error=login_failed');
        }
    }
    public function exchangeCode(Request $request)
    {
        $code = $request->input('code');
        $cacheKey = 'oauth_code_' . $code;
        $token = Cache::get($cacheKey);
        if (!$token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn.'
            ], 401);
        }
        Cache::forget($cacheKey);
        return response()->json([
            'status' => 'success',
            'token'  => $token,
        ]);
    }
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'user'   => [
                'id'        => $user->id_nguoidung,
                'ten'       => $user->ten,
                'email'     => $user->email,
                'sdt'       => $user->sdt,
                'ngaysinh'  => $user->ngaysinh,
                'gioitinh'  => $user->gioitinh,
                'avatar'    => $user->avatar,
                'mancc'     => $user->mancc,
                'phanquyen' => $user->phanquyen,
            ]
        ]);
    }
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'ten' => 'sometimes|string|max:255',
            'sdt' => 'sometimes|string|max:20',
            'ngaysinh' => 'nullable|date',
            'gioitinh' => 'nullable|string|max:10',
        ]);
        if ($request->has('ten')) {
            $user->ten = $request->ten;
        }
        if ($request->has('sdt')) {
            $user->sdt = $request->sdt;
        }
        if ($request->has('ngaysinh')) {
            $user->ngaysinh = $request->ngaysinh;
        }
        if ($request->has('gioitinh')) {
            $user->gioitinh = $request->gioitinh;
        }
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật tài khoản thành công',
            'user' => [
                'id'        => $user->id_nguoidung,
                'ten'       => $user->ten,
                'email'     => $user->email,
                'sdt'       => $user->sdt,
                'ngaysinh'  => $user->ngaysinh,
                'gioitinh'  => $user->gioitinh,
                'avatar'    => $user->avatar,
                'mancc'     => $user->mancc,
                'phanquyen' => $user->phanquyen,
            ]
        ]);
    }
    public function sendRegisterOTP(RegisterRequest $request){
        $otp = rand(100000,999999);
        $identifier = $request->email ? $request->email : $request->sdt;
        Cache::put('register_otp_'.$identifier,
        ['otp'=>$otp,
        'data'=>$request->all()],now()->addMinutes(5));
        if($request->email){
            try{
                Mail::to($request->email)->send(new OtpMail($otp,$request->ten,'register'));
            }catch(\Exception $e){
                return response()->json([
                    'status'=> 'error',
                    'message'=> 'Không thể gửi mã OTP. Vui lòng thử lại',
                ],500);
            }
        }
        return response()->json([
            'status'=> 'success',
            'message'=> 'Đã gửi mã OTP thành công',
        ]);
    }
    public function verifyRegisterOTP(Request $request){
        $identifier = $request->email ? $request->email : $request->sdt;
        $cachedData = Cache::get('register_otp_'.$identifier);
        if(!$cachedData || $cachedData['otp'] != $request->otp){
            return response()->json([
                'status' => 'error',
                'message' => 'Mã OTP không chính xác hoặc đã hết hạn',
            ]);
        }
        $data = $cachedData['data'];
          $user = Nguoi_dung::create([
            'ten'       => $data['ho'] . ' ' . $data['ten'],
            'email'     => $data['email'] ?? null,
            'sdt'       => $data['sdt'] ?? null,
            'matkhau'   => Hash::make($data['matkhau']),
            'phanquyen' => 3,
        ]);
        ThongBao::create([
            'loai_thong_bao' => 'USER',
            'tieu_de'        => 'Khách hàng mới',
            'noi_dung'       => 'Khách hàng ' . $user->ten . ' vừa đăng ký tài khoản.',
            'link'           => '/admin/nguoi-dung'
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        Cache::forget('register_otp_' . $identifier);
        return response()->json([
            'status'  => 'success',
            'message' => 'Xác thực OTP và Đăng ký thành công!',
            'token'   => $token,
            'user'    => $user
        ]);

    }
}
