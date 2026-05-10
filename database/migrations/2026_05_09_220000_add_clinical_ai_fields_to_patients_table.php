<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->longText('clinical_ai_summary')->nullable();
            $table->timestamp('clinical_ai_summary_generated_at')->nullable();
            $table->boolean('ai_summary_disabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'clinical_ai_summary',
                'clinical_ai_summary_generated_at',
                'ai_summary_disabled',
            ]);
        });
    }
};
