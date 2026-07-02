<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP - TOIYEUPC</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5;
            padding: 40px 20px;
        }
        .wrapper {
            max-width: 560px;
            margin: 0 auto;
        }
        /* Header */
        .header {
            background: linear-gradient(135deg, #e30019 0%, #b8001a 100%);
            border-radius: 12px 12px 0 0;
            padding: 32px 40px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 2px;
        }
        .logo span { color: #ffcc00; }
        .tagline {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            margin-top: 4px;
            letter-spacing: 1px;
        }
        /* Body */
        .body {
            background: #ffffff;
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 12px;
        }
        .message {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        /* OTP Box */
        .otp-label {
            font-size: 13px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-align: center;
            margin-bottom: 12px;
        }
        .otp-box {
            background: linear-gradient(135deg, #fff5f5 0%, #fff0f0 100%);
            border: 2px dashed #e30019;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            margin-bottom: 32px;
        }
        .otp-code {
            font-size: 52px;
            font-weight: 900;
            color: #e30019;
            letter-spacing: 12px;
            font-family: 'Courier New', monospace;
            line-height: 1;
        }
        .otp-expire {
            margin-top: 12px;
            font-size: 13px;
            color: #e07800;
            font-weight: 500;
        }
        .otp-expire strong { color: #e30019; }
        /* Warning */
        .warning-box {
            background: #fffbea;
            border-left: 4px solid #f59e0b;
            border-radius: 0 8px 8px 0;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .warning-box p {
            font-size: 13px;
            color: #92400e;
            line-height: 1.6;
        }
        .warning-box strong { color: #78350f; }
        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 28px 0;
        }
        .footer-note {
            font-size: 13px;
            color: #999;
            text-align: center;
            line-height: 1.7;
        }
        /* Footer */
        .footer {
            background: #f8f8f8;
            border-radius: 0 0 12px 12px;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .footer p {
            font-size: 12px;
            color: #aaa;
            line-height: 1.8;
        }
        .footer a {
            color: #e30019;
            text-decoration: none;
        }
        .shop-info {
            margin-top: 12px;
            font-size: 12px;
            color: #bbb;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="logo">TOI<span>YEU</span>PC</div>
            <div class="tagline">Thiên đường máy tính & linh kiện</div>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Xin chào, {{ $userName }}! 👋</p>
            <p class="message">
                Chúng tôi nhận được yêu cầu <strong>đặt lại mật khẩu</strong> cho tài khoản của bạn
                tại <strong>TOIYEUPC</strong>.<br><br>
                Sử dụng mã OTP bên dưới để tiếp tục. Mã này chỉ có hiệu lực trong <strong>10 phút</strong>.
            </p>

            <!-- OTP Code -->
            <div class="otp-label">🔐 Mã xác nhận của bạn</div>
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expire">
                    ⏰ Mã hết hạn sau <strong>10 phút</strong>
                </div>
            </div>

            <!-- Warning -->
            <div class="warning-box">
                <p>
                    ⚠️ <strong>Lưu ý bảo mật:</strong> Không chia sẻ mã này với bất kỳ ai —
                    kể cả nhân viên TOIYEUPC. Nếu bạn không yêu cầu đặt lại mật khẩu,
                    hãy bỏ qua email này. Tài khoản của bạn vẫn an toàn.
                </p>
            </div>

            <hr class="divider">

            <p class="footer-note">
                Email này được gửi tự động từ hệ thống TOIYEUPC.<br>
                Vui lòng không trả lời email này.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                © {{ date('Y') }} <strong>TOIYEUPC</strong> — Thiên đường máy tính & linh kiện<br>
                Hotline: <a href="tel:0941061704">0941 061 704</a> &nbsp;|&nbsp;
                Email: <a href="mailto:phamtrithanh1234@gmail.com">phamtrithanh1234@gmail.com</a>
            </p>
            <div class="shop-info">
                Nếu bạn có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ của chúng tôi.
            </div>
        </div>
    </div>
</body>
</html>
