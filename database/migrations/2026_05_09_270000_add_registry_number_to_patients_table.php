<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('registry_number', 36)->nullable()->unique()->after('uuid');
        });

        $prefix = config('isnad.registry.prefix', 'ISN');
        $length = max(6, min(12, (int) config('isnad.registry.sequence_length', 8)));

        foreach (DB::table('patients')->orderBy('id')->pluck('id') as $id) {
            DB::table('patients')->where('id', $id)->update([
                'registry_number' => $prefix.'-'.str_pad((string) $id, $length, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['registry_number']);
            $table->dropColumn('registry_number');
        });
    }
};
