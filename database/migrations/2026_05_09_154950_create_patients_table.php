<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('full_name');
            $table->string('phone')->index();
            $table->string('national_id')->nullable()->index();

            $table->date('birth_date')->nullable();
            $table->string('blood_type')->nullable();

            $table->text('chronic_diseases')->nullable();
            $table->text('allergies')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
