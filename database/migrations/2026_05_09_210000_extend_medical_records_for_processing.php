<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('enhanced_file_path')->nullable();
            $table->longText('ocr_text')->nullable();
            $table->longText('ai_summary')->nullable();
            $table->string('processing_status')->default('pending');
            $table->timestamp('processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'enhanced_file_path',
                'ocr_text',
                'ai_summary',
                'processing_status',
                'processed_at',
            ]);
        });
    }
};
