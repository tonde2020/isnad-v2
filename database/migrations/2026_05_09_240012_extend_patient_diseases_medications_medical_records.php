<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_diseases', function (Blueprint $table) {
            $table->date('diagnosed_at')->nullable()->after('status');
            $table->string('severity', 32)->nullable()->after('diagnosed_at');
            $table->string('source', 32)->nullable()->default('admin')->after('severity');
            $table->boolean('is_confirmed')->default(false)->after('source');
            $table->foreignId('confirmed_by')->nullable()->after('is_confirmed')->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });

        Schema::table('patient_medications', function (Blueprint $table) {
            $table->date('stopped_at')->nullable()->after('start_date');
            $table->text('stop_reason')->nullable()->after('stopped_at');
            $table->string('source', 32)->nullable()->default('admin')->after('stop_reason');
            $table->boolean('is_confirmed')->default(false)->after('source');
            $table->foreignId('confirmed_by')->nullable()->after('is_confirmed')->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('record_type', 32)->nullable()->after('title');
            $table->timestamp('uploaded_at')->nullable()->after('record_date');
            $table->boolean('is_reviewed')->default(false)->after('uploaded_at');
            $table->foreignId('reviewed_by')->nullable()->after('is_reviewed')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('patient_diseases', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn([
                'diagnosed_at',
                'severity',
                'source',
                'is_confirmed',
                'confirmed_by',
                'confirmed_at',
            ]);
        });

        Schema::table('patient_medications', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn([
                'stopped_at',
                'stop_reason',
                'source',
                'is_confirmed',
                'confirmed_by',
                'confirmed_at',
            ]);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'record_type',
                'uploaded_at',
                'is_reviewed',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};
