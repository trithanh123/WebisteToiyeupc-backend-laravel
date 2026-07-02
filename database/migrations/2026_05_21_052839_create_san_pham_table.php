<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        Schema::create('san_pham', function (Blueprint $table) {
            $table->id('id_sanpham');
            $table->unsignedBigInteger('ma_danhmuc');
            $table->string('masp', 100)->unique();
            $table->string('tensp', 255);
            $table->bigInteger('gia');
            $table->string('thumbail', 255)->nullable();
            $table->text('motasanpham')->nullable();
            $table->jsonb('specifications')->nullable();
            $table->timestamps();

            $table->foreign('ma_danhmuc')
                  ->references('id_danhmuc')
                  ->on('danh_muc')
                  ->onDelete('cascade');
        });

        DB::statement('ALTER TABLE san_pham ADD COLUMN embedding vector(768);');
        DB::statement('CREATE INDEX san_pham_embedding_hnsw_idx ON san_pham USING hnsw (embedding vector_cosine_ops);');
        DB::statement('CREATE INDEX san_pham_specifications_gin_idx ON san_pham USING gin (specifications);');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS san_pham_embedding_hnsw_idx;');
        DB::statement('DROP INDEX IF EXISTS san_pham_specifications_gin_idx;');
        Schema::dropIfExists('san_pham');
    }
};