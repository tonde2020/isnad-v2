<?php

namespace Tests\Feature;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Support\MedicalRecordExtractionDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordExtractionDraftPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_persist_clears_stored_draft_when_form_state_empty(): void
    {
        $patient = Patient::factory()->create();

        $record = MedicalRecord::query()->create([
            'patient_id' => $patient->getKey(),
            'title' => 'مرفق',
            'file_path' => 'medical-records/d.pdf',
            'processing_status' => 'completed',
            'extraction_review_draft' => [
                'condition_indices' => [0],
                'allergy_indices' => [],
                'medication_indices' => [],
                'lab_indices' => [],
                'condition_name_fixes' => [],
                'allergy_name_fixes' => [],
                'medication_name_fixes' => [],
            ],
        ]);

        $result = MedicalRecordExtractionDraft::persistForRecord($record, [
            'condition_indices' => [],
            'allergy_indices' => [],
            'medication_indices' => [],
            'lab_indices' => [],
            'condition_name_fixes' => [],
            'allergy_name_fixes' => [],
            'medication_name_fixes' => [],
        ]);

        $this->assertTrue($result['had_stored_draft']);
        $this->assertNull($result['sanitized']);

        $record->refresh();
        $this->assertNull($record->extraction_review_draft);
    }
}
