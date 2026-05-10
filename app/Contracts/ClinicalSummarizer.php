<?php

namespace App\Contracts;

interface ClinicalSummarizer
{
    /**
     * @param  array<string, mixed>  $anonymizedContext
     */
    public function summarizePatientSnapshot(array $anonymizedContext): string;

    public function isAvailable(): bool;
}
