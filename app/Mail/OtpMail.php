<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class OtpMail extends Mailable
{
    use Queueable, SerializesModels;
    public string $otp;
    public string $userName;
    public string $type;
    public function __construct(string $otp, string $userName = 'Quý khách', string $type = 'reset')
    {
        $this->otp      = $otp;
        $this->userName = $userName;
        $this->type     = $type;  
    }
    public function envelope(): Envelope
    {
        $subject = $this->type === 'register'
            ? '🎉 [TOIYEUPC] Mã xác nhận đăng ký tài khoản'
            : '🔐 [TOIYEUPC] Mã xác nhận đặt lại mật khẩu';
        return new Envelope(subject: $subject);
    }
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
