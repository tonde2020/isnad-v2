<?php

namespace Tests\Feature;

use App\Enums\PatientDiseaseKind;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientDisease;
use App\Models\PatientMedication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalAuditFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_disease_fills_confirmed_by_when_authenticated(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user);

        $disease = PatientDisease::query()->create([
            'patient_id' => $patient->getKey(),
            'disease_master_id' => null,
            'kind' => PatientDiseaseKind::Chronic,
            'custom_name' => 'مرض تجريبي',
            'status' => 'active',
            'is_confirmed' => true,
            'source' => 'admin',
        ]);

        $this->assertSame($user->getKey(), $disease->fresh()->confirmed_by);
        $this->assertNotNull($disease->fresh()->confirmed_at);
    }

    public function test_confirming_medication_fills_confirmed_by_when_authenticated(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user);

        $medication = PatientMedication::query()->create([
            'patient_id' => $patient->getKey(),
            'medication_master_id' => null,
            'custom_medication_name' => 'دواء تجريبي',
            'is_active' => true,
            'is_confirmed' => true,
            'source' => 'admin',
        ]);

        $this->assertSame($user->getKey(), $medication->fresh()->confirmed_by);
        $this->assertNotNull($medication->fresh()->confirmed_at);
    }

    public function test_reviewing_medical_record_fills_reviewed_by_when_authenticated(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user);

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->getKey(),
            'title' => 'مرفق',
            'file_path' => 'medical-records/z.pdf',
            'is_reviewed' => true,
            'processing_status' => 'pending',
        ]);

        $this->assertSame($user->getKey(), $record->fresh()->reviewed_by);
        $this->assertNotNull($record->fresh()->reviewed_at);
    }
}
