<?php

namespace App\Models;

use Database\Factories\ThongBaoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    protected $table = 'thong_bao';
    protected $primaryKey = 'id_thongbao';

    protected $fillable = [
        'loai_thong_bao',
        'tieu_de',
        'noi_dung',
        'nguoi_doc',
        'link'
    ];

    protected $casts = [
        'nguoi_doc' => 'array', // Tự động decode JSON thành array PHP
    ];

    protected static function newFactory(): ThongBaoFactory
    {
        return ThongBaoFactory::new();
    }
}
