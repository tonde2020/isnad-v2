<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_diseases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disease_master_id')->nullable()->constrained('disease_masters')->nullOnDelete();

            $table->string('kind')->default('chronic');
            $table->string('custom_name')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_diseases');
    }
};
