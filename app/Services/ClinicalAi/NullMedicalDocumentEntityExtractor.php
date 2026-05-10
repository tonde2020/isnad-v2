<?php

namespace App\Services\ClinicalAi;

use App\Contracts\MedicalDocumentEntityExtractor;

final class NullMedicalDocumentEntityExtractor implements MedicalDocumentEntityExtractor
{
    public function extractFromOcrText(string $ocrText): ?array
    {
        return null;
    }

    public function isAvailable(): bool
    {
        return false;
    }
}
