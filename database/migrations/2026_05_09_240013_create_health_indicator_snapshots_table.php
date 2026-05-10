<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_indicator_snapshots', function (Blueprint $table) {
            $table->id();

            $table->date('snapshot_date');
            $table->string('region_key', 64)->default('national');

            $table->json('payload');

            $table->timestamps();

            $table->unique(['snapshot_date', 'region_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_indicator_snapshots');
    }
};
