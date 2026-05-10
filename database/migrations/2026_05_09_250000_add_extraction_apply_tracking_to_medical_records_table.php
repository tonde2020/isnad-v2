<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->timestamp('extracted_entities_applied_at')->nullable()->after('extracted_entities');
            $table->foreignId('extracted_entities_applied_by')->nullable()->after('extracted_entities_applied_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['extracted_entities_applied_by']);
            $table->dropColumn([
                'extracted_entities_applied_at',
                'extracted_entities_applied_by',
            ]);
        });
    }
};
