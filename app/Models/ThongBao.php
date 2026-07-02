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
        'da_doc',
        'link'
    ];

    protected $casts = [
        'da_doc' => 'boolean',
    ];

    protected static function newFactory(): ThongBaoFactory
    {
        return ThongBaoFactory::new();
    }
}
