<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_share_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->uuid('token')->unique();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone')->nullable();

            $table->json('allowed_sections')->nullable();
            $table->json('allowed_record_ids')->nullable();

            $table->timestamp('expires_at');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_share_sessions');
    }
};
