<?php

namespace Tests\Feature;

use App\Filament\Resources\Patients\PatientResource;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewMedicalRecordExtractionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_owner_can_open_review_extraction_workspace(): void
    {
        $user = User::factory()->patient()->create();
        $patient = Patient::factory()->create(['user_id' => $user->getKey()]);

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->getKey(),
            'title' => 'تحليل',
            'file_path' => 'medical-records/t.pdf',
            'ocr_text' => 'نص OCR للاختبار',
            'extracted_entities' => [
                'conditions' => [['name' => 'ضغط', 'confidence' => 0.5]],
                'allergies' => [],
                'medications' => [],
            ],
            'processing_status' => 'completed',
        ]);

        $url = PatientResource::getUrl('reviewMedicalRecordExtraction', [
            'record' => $patient->getKey(),
            'medicalRecord' => $record->getKey(),
        ]);

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        $this->actingAs($user)->get((string) $path)->assertOk();
    }

    public function test_operational_admin_cannot_open_review_extraction_workspace(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = Patient::factory()->create();

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->getKey(),
            'title' => 'مرفق',
            'file_path' => 'medical-records/a.pdf',
            'extracted_entities' => [
                'conditions' => [['name' => 'X', 'confidence' => 0.1]],
                'allergies' => [],
                'medications' => [],
            ],
            'processing_status' => 'completed',
        ]);

        $url = PatientResource::getUrl('reviewMedicalRecordExtraction', [
            'record' => $patient->getKey(),
            'medicalRecord' => $record->getKey(),
        ]);

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        $this->actingAs($admin)->get((string) $path)->assertForbidden();
    }
}
