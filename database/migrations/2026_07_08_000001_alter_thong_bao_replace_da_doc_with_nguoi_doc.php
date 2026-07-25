<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Xóa cột da_doc (boolean toàn cục) và thêm cột nguoi_doc (JSON per-user).
     */
    public function up(): void
    {
        Schema::table('thong_bao', function (Blueprint $table) {
            $table->dropColumn('da_doc');
            $table->json('nguoi_doc')->default('[]')->after('noi_dung'); // Danh sách id người đã đọc
        });
    }

    /**
     * Hoàn tác: xóa nguoi_doc, thêm lại da_doc.
     */
    public function down(): void
    {
        Schema::table('thong_bao', function (Blueprint $table) {
            $table->dropColumn('nguoi_doc');
            $table->boolean('da_doc')->default(false)->after('noi_dung');
        });
    }
};
