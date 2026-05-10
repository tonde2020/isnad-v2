<?php

namespace App\Contracts;

interface MedicalDocumentEntityExtractor
{
    /**
     * @return array<string, mixed>|null
     */
    public function extractFromOcrText(string $ocrText): ?array;

    public function isAvailable(): bool;
}
