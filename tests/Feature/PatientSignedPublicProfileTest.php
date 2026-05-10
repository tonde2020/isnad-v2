<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Support\PatientTemporaryProfileLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientSignedPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_public_profile_request_is_rejected(): void
    {
        $patient = Patient::factory()->create();

        $this->get('/p/'.$patient->uuid)
            ->assertForbidden();
    }

    public function test_signed_public_profile_request_succeeds(): void
    {
        $patient = Patient::factory()->create();

        $url = PatientTemporaryProfileLink::publicProfileUrl($patient);

        $this->get($url)
            ->assertOk()
            ->assertSeeText($patient->full_name, false);
    }

    public function test_signed_pdf_request_succeeds(): void
    {
        $patient = Patient::factory()->create();

        $url = PatientTemporaryProfileLink::pdfUrl($patient);

        $this->get($url)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_signed_medical_record_file_stream_succeeds(): void
    {
        Storage::fake('medical_private');

        $patient = Patient::factory()->create();
        $path = 'medical-records/test-doc.pdf';
        Storage::disk('medical_private')->put($path, '%PDF-1.4 test');

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->id,
            'title' => 'تحليل',
            'file_path' => $path,
            'processing_status' => 'pending',
        ]);

        $url = PatientTemporaryProfileLink::medicalRecordFileUrl($patient, $record);

        $this->get($url)->assertOk();
    }

    public function test_medical_record_stream_rejects_wrong_patient(): void
    {
        Storage::fake('medical_private');

        $patientA = Patient::factory()->create();
        $patientB = Patient::factory()->create();
        $path = 'medical-records/other.pdf';
        Storage::disk('medical_private')->put($path, 'x');

        $record = MedicalRecord::query()->create([
            'patient_id' => $patientB->id,
            'title' => 'سري',
            'file_path' => $path,
            'processing_status' => 'pending',
        ]);

        $url = PatientTemporaryProfileLink::medicalRecordFileUrl($patientA, $record);

        $this->get($url)->assertNotFound();
    }

    public function test_signed_medical_record_original_stream_succeeds(): void
    {
        Storage::fake('medical_private');

        $patient = Patient::factory()->create();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $path = 'medical-records/orig.png';
        Storage::disk('medical_private')->put($path, $png);

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->id,
            'title' => 'مرفق',
            'file_path' => $path,
            'processing_status' => 'pending',
        ]);

        $record->refresh();

        $url = PatientTemporaryProfileLink::medicalRecordOriginalFileUrl($patient, $record);

        $this->get($url)->assertOk();
    }
}
