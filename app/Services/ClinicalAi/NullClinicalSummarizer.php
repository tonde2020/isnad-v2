<?php

namespace App\Services\ClinicalAi;

use App\Contracts\ClinicalSummarizer;

final class NullClinicalSummarizer implements ClinicalSummarizer
{
    public function summarizePatientSnapshot(array $anonymizedContext): string
    {
        return '';
    }

    public function isAvailable(): bool
    {
        return false;
    }
}
