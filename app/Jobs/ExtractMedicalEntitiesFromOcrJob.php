<?php

namespace App\Jobs;

use App\Contracts\MedicalDocumentEntityExtractor;
use App\Models\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExtractMedicalEntitiesFromOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $medicalRecordId,
    ) {}

    public function handle(MedicalDocumentEntityExtractor $extractor): void
    {
        $record = MedicalRecord::query()->find($this->medicalRecordId);

        if ($record === null || blank($record->ocr_text)) {
            return;
        }

        if (! $extractor->isAvailable()) {
            return;
        }

        try {
            $entities = $extractor->extractFromOcrText((string) $record->ocr_text);

            if ($entities !== null && $entities !== []) {
                $record->update(['extracted_entities' => $entities]);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
