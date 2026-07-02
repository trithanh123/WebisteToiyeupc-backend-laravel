<?php
namespace App\Models;
use Database\Factories\OtpTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class OtpToken extends Model
{
    use HasFactory;
    protected $table = 'otp_tokens';
    protected $fillable = [
        'identifier',
        'token',
        'expires_at',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    protected static function newFactory(): OtpTokenFactory
    {
        return OtpTokenFactory::new();
    }
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }
}
