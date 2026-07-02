<?php

namespace App\Models;

use Database\Factories\LienHeFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class lien_he extends Model
{
    use HasFactory;

    protected $table      = 'lien_he';
    protected $primaryKey = 'id_lienhe';

    protected $fillable = [
        'ma_nguoidung', 'ten_lienhe', 'email_lienhe',
        'sdt', 'website', 'noidung', 'trangthai',
    ];

    protected $casts = [
        'trangthai' => 'integer',
    ];

    protected static function newFactory(): LienHeFactory
    {
        return LienHeFactory::new();
    }

    public function nguoiDung()
    {
        return $this->belongsTo(Nguoi_dung::class, 'ma_nguoidung', 'id_nguoidung');
    }
}
