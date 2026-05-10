<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            DB::table('users')->whereIn('role', ['staff', 'viewer'])->update(['role' => 'admin']);
        }

        foreach (['patient_diseases', 'patient_medications', 'patient_medical_events'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->where('source', 'staff')->update(['source' => 'admin']);
        }
    }
};
