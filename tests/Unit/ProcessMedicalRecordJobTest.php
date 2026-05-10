<?php

namespace Tests\Unit;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessMedicalRecordJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_runs_pipeline_and_stores_enhanced_copy(): void
    {
        Storage::fake('medical_private');

        $patient = Patient::factory()->create();

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $path = 'medical-records/tiny.png';
        Storage::disk('medical_private')->put($path, $png);

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->id,
            'title' => 'صورة',
            'file_path' => $path,
            'processing_status' => 'pending',
        ]);

        $record->refresh();

        $this->assertSame('completed', $record->processing_status);
        $this->assertNotNull($record->enhanced_file_path);
        $this->assertTrue(Storage::disk('medical_private')->exists($record->enhanced_file_path));
    }
}
