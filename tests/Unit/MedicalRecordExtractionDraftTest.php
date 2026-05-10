<?php

namespace Tests\Unit;

use App\Support\MedicalRecordExtractionDraft;
use PHPUnit\Framework\TestCase;

class MedicalRecordExtractionDraftTest extends TestCase
{
    public function test_sanitize_keeps_zero_index_selections(): void
    {
        $out = MedicalRecordExtractionDraft::sanitizeForStorage([
            'condition_indices' => [0],
            'allergy_indices' => [],
            'medication_indices' => [],
            'lab_indices' => [],
            'condition_name_fixes' => [],
            'allergy_name_fixes' => [],
            'medication_name_fixes' => [],
        ]);

        $this->assertIsArray($out);
        $this->assertSame([0], $out['condition_indices']);
    }

    public function test_sanitize_returns_null_when_completely_empty(): void
    {
        $this->assertNull(MedicalRecordExtractionDraft::sanitizeForStorage([
            'condition_indices' => [],
            'allergy_indices' => [],
            'medication_indices' => [],
            'lab_indices' => [],
            'condition_name_fixes' => [],
            'allergy_name_fixes' => [],
            'medication_name_fixes' => [],
        ]));
    }
}
