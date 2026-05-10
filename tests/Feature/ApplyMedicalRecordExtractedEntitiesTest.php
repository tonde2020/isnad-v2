<?php

namespace Tests\Feature;

use App\Enums\PatientDiseaseKind;
use App\Models\DiseaseMaster;
use App\Models\MedicalRecord;
use App\Models\MedicationMaster;
use App\Models\Patient;
use App\Models\PatientDisease;
use App\Models\PatientMedicalEvent;
use App\Models\PatientMedication;
use App\Models\User;
use App\Services\ApplyMedicalRecordExtractedEntities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyMedicalRecordExtractedEntitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_creates_chronic_allergy_and_medication_rows_and_marks_record_applied(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $patient = Patient::factory()->create();

        DiseaseMaster::query()->create([
            'name_ar' => 'سكري النوع الثاني',
            'name_en' => 'Type 2 diabetes',
            'category' => 'chronic',
            'is_active' => true,
        ]);

        DiseaseMaster::query()->create([
            'name_ar' => 'حساسية البنسلين',
            'name_en' => 'Penicillin allergy',
            'category' => 'allergy',
            'is_active' => true,
        ]);

        MedicationMaster::query()->create([
            'brand_name' => 'بانادول',
            'generic_name' => 'باراسيتامول',
            'is_active' => true,
        ]);

        $record = new MedicalRecord([
            'patient_id' => $patient->getKey(),
            'title' => 'تقرير',
            'file_path' => 'medical-records/test.pdf',
            'processing_status' => 'completed',
            'extracted_entities' => [
                'conditions' => [['name' => 'سكري', 'confidence' => 0.9]],
                'allergies' => [['name' => 'بنسلين', 'confidence' => 0.8]],
                'medications' => [['name' => 'بانادول', 'dosage' => '500mg', 'confidence' => 0.7]],
                'lab_results' => [],
                'notes' => '',
            ],
        ]);
        $record->saveQuietly();

        $record->forceFill([
            'extraction_review_draft' => [
                'condition_indices' => [0],
                'allergy_indices' => [],
                'medication_indices' => [],
                'lab_indices' => [],
                'condition_name_fixes' => [],
                'allergy_name_fixes' => [],
                'medication_name_fixes' => [],
            ],
        ])->saveQuietly();

        $this->actingAs($user);

        $stats = app(ApplyMedicalRecordExtractedEntities::class)->apply($record, [
            'conditions' => [0],
            'allergies' => [0],
            'medications' => [0],
        ], $user->getKey());

        $this->assertSame(2, $stats['diseases_created']);
        $this->assertSame(1, $stats['medications_created']);
        $this->assertSame(0, $stats['skipped_duplicates']);
        $this->assertSame(0, $stats['lab_timeline_events']);

        $this->assertSame(1, PatientDisease::query()->where('patient_id', $patient->getKey())->where('kind', PatientDiseaseKind::Chronic)->count());
        $this->assertSame(1, PatientDisease::query()->where('patient_id', $patient->getKey())->where('kind', PatientDiseaseKind::Allergy)->count());
        $this->assertSame(1, PatientMedication::query()->where('patient_id', $patient->getKey())->count());

        $record->refresh();

        $this->assertNotNull($record->extracted_entities_applied_at);
        $this->assertSame($user->getKey(), $record->extracted_entities_applied_by);
        $this->assertNull($record->extraction_review_draft);
        $this->assertTrue($record->is_reviewed);
        $this->assertSame($user->getKey(), $record->reviewed_by);
    }

    public function test_second_apply_is_noop(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        DiseaseMaster::query()->create([
            'name_ar' => 'ضغط دم',
            'name_en' => 'Hypertension',
            'category' => 'chronic',
            'is_active' => true,
        ]);

        $record = new MedicalRecord([
            'patient_id' => $patient->getKey(),
            'title' => 'تحليل',
            'file_path' => 'medical-records/a.pdf',
            'processing_status' => 'completed',
            'extracted_entities' => [
                'conditions' => [['name' => 'ضغط', 'confidence' => 0.5]],
                'allergies' => [],
                'medications' => [],
            ],
        ]);
        $record->saveQuietly();

        $this->actingAs($user);

        $service = app(ApplyMedicalRecordExtractedEntities::class);

        $service->apply($record, ['conditions' => [0], 'allergies' => [], 'medications' => []], $user->getKey());

        $record->refresh();

        $stats = $service->apply($record, ['conditions' => [0], 'allergies' => [], 'medications' => []], $user->getKey());

        $this->assertSame(0, $stats['diseases_created']);
        $this->assertSame(0, $stats['lab_timeline_events']);
        $this->assertSame(1, PatientDisease::query()->where('patient_id', $patient->getKey())->count());
    }

    public function test_lab_selection_only_adds_timeline_event(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $record = new MedicalRecord([
            'patient_id' => $patient->getKey(),
            'title' => 'تحليل',
            'file_path' => 'medical-records/labs.pdf',
            'processing_status' => 'completed',
            'ocr_text' => 'CBC ...',
            'extracted_entities' => [
                'conditions' => [],
                'allergies' => [],
                'medications' => [],
                'lab_results' => [
                    ['test_name' => 'الهيموجلوبين', 'value' => '13', 'unit' => 'g/dL', 'reference_range' => '12-16', 'confidence' => 0.9],
                ],
                'notes' => '',
            ],
        ]);
        $record->saveQuietly();

        $this->actingAs($user);

        $stats = app(ApplyMedicalRecordExtractedEntities::class)->apply($record, [
            'conditions' => [],
            'allergies' => [],
            'medications' => [],
            'lab_indices' => [0],
        ], $user->getKey());

        $this->assertSame(0, $stats['diseases_created']);
        $this->assertSame(0, $stats['medications_created']);
        $this->assertSame(1, $stats['lab_timeline_events']);

        $this->assertSame(1, PatientMedicalEvent::query()->where('patient_id', $patient->getKey())->count());

        $record->refresh();
        $this->assertNotNull($record->extracted_entities_applied_at);
    }

    public function test_condition_name_fix_used_for_master_matching(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        DiseaseMaster::query()->create([
            'name_ar' => 'السكري من النوع الثاني',
            'name_en' => 'T2DM',
            'category' => 'chronic',
            'is_active' => true,
        ]);

        $record = new MedicalRecord([
            'patient_id' => $patient->getKey(),
            'title' => 'تقرير',
            'file_path' => 'medical-records/r.pdf',
            'processing_status' => 'completed',
            'extracted_entities' => [
                'conditions' => [['name' => 'DM', 'confidence' => 0.4]],
                'allergies' => [],
                'medications' => [],
            ],
        ]);
        $record->saveQuietly();

        $this->actingAs($user);

        $stats = app(ApplyMedicalRecordExtractedEntities::class)->apply($record, [
            'conditions' => [0],
            'allergies' => [],
            'medications' => [],
            'condition_name_fixes' => ['0' => 'السكري'],
        ], $user->getKey());

        $this->assertSame(1, $stats['diseases_created']);
        $this->assertSame(1, PatientDisease::query()->where('patient_id', $patient->getKey())->whereNotNull('disease_master_id')->count());
    }
}
