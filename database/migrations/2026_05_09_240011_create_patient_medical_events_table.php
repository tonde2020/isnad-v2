<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medical_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->string('event_type', 64)->index();
            $table->date('event_date')->index();
            $table->time('event_time')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('source', 32)->default('admin');

            $table->longText('metadata')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medical_events');
    }
};
