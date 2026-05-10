<?php

namespace Tests\Feature;

use App\Jobs\ExtractMedicalEntitiesFromOcrJob;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExtractMedicalEntitiesFromOcrJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_persists_extracted_entities_when_openai_returns_json(): void
    {
        config(['isnad.documents.extraction_enabled' => true]);
        config(['isnad.ai.api_key' => 'sk-test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'medications' => [],
                            'conditions' => [['name' => 'DM', 'confidence' => 0.8]],
                            'allergies' => [],
                            'lab_results' => [],
                            'notes' => 'اختبار',
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $patient = Patient::factory()->create();

        $record = new MedicalRecord([
            'patient_id' => $patient->id,
            'title' => 'روشتة',
            'file_path' => 'medical-records/x.png',
            'ocr_text' => 'نص تجريبي من OCR',
            'processing_status' => 'completed',
        ]);
        $record->saveQuietly();

        Bus::dispatchSync(new ExtractMedicalEntitiesFromOcrJob($record->getKey()));

        $record->refresh();

        $this->assertIsArray($record->extracted_entities);
        $this->assertArrayHasKey('conditions', $record->extracted_entities);
    }
}
