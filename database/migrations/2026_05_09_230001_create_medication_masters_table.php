<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_masters', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->nullable();
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('form')->nullable();
            $table->string('manufacturer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('brand_name');
            $table->index('generic_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_masters');
    }
};
