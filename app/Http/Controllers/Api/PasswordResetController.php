<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Nguoi_dung;
use App\Models\OtpToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\sendOtpRequest;
use App\Http\Requests\verifyOtpRequest;
use App\Http\Requests\resetPasswordRequest;
class PasswordResetController extends Controller
{
    public function sendOtp(sendOtpRequest $request)
    {
        $identifier = trim($request->identifier);
        $user = Nguoi_dung::where('email', $identifier)
                          ->orWhere('sdt', $identifier)
                          ->first();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy tài khoản với email hoặc số điện thoại này!',
            ], 404);
        }
        OtpToken::where('identifier', $identifier)->delete();
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpToken::create([
            'identifier' => $identifier,
            'token'      => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
        ]);
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            Mail::to($identifier)->send(new OtpMail($otp, $user->ten));
        }
        return response()->json([
            'status'  => 'success',
            'message' => 'Mã OTP đã được gửi! Vui lòng kiểm tra email của bạn.',
            'type'    => filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone',
        ]);
    }
    public function verifyOtp(verifyOtpRequest $request)
    {   
        $identifier = trim($request->identifier);
        $otpRecord = OtpToken::where('identifier', $identifier)
                             ->latest()
                             ->first();
        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mã OTP không chính xác!',
            ], 400);
        }
        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            return response()->json([
                'status'  => 'error',
                'message' => 'Mã OTP đã hết hạn! Vui lòng yêu cầu mã mới.',
            ], 400);
        }
        return response()->json([
            'status'      => 'success',
            'message'     => 'Xác thực OTP thành công!',
            'identifier'  => $identifier, 
        ]);
    }
    public function resetPassword(resetPasswordRequest $request)
    {
        $identifier = trim($request->identifier);
        $otpRecord = OtpToken::where('identifier', $identifier)
                             ->latest()
                             ->first();
        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->token) || $otpRecord->isExpired()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Phiên xác thực đã hết hạn, vui lòng bắt đầu lại!',
            ], 400);
        }
        $user = Nguoi_dung::where('email', $identifier)
                          ->orWhere('sdt', $identifier)
                          ->first();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy tài khoản!',
            ], 404);
        }
        $user->update([
            'matkhau' => Hash::make($request->password),
        ]);
        OtpToken::where('identifier', $identifier)->delete();
        $user->tokens()->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại.',
        ]);
    }
}
