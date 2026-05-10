<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('uuid')->constrained()->nullOnDelete();

            $table->string('gender', 20)->nullable()->after('blood_type');
            $table->string('state', 120)->nullable()->after('gender');
            $table->string('locality', 120)->nullable()->after('state');
            $table->string('displacement_area', 255)->nullable()->after('locality');
            $table->string('emergency_contact_name', 255)->nullable()->after('displacement_area');
            $table->string('emergency_contact_phone', 64)->nullable()->after('emergency_contact_name');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'gender',
                'state',
                'locality',
                'displacement_area',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
