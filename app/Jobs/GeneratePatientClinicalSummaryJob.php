<?php

namespace App\Jobs;

use App\Contracts\ClinicalSummarizer;
use App\Models\ClinicalAiAuditLog;
use App\Models\Patient;
use App\Support\PatientClinicalContextBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GeneratePatientClinicalSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $patientId,
        public ?int $requestedByUserId = null,
    ) {}

    public function handle(ClinicalSummarizer $summarizer): void
    {
        $patient = Patient::query()->find($this->patientId);

        if ($patient === null || $patient->ai_summary_disabled) {
            return;
        }

        if (! $summarizer->isAvailable()) {
            ClinicalAiAuditLog::query()->create([
                'patient_id' => $patient->getKey(),
                'user_id' => $this->requestedByUserId,
                'action' => 'patient_clinical_summary',
                'success' => false,
                'error_message' => 'AI summarizer not configured or disabled.',
            ]);

            return;
        }

        try {
            $payload = PatientClinicalContextBuilder::forAiSummary($patient);
            $summary = $summarizer->summarizePatientSnapshot($payload);

            if ($summary === '') {
                throw new \RuntimeException('Empty summary returned.');
            }

            $patient->forceFill([
                'clinical_ai_summary' => $summary,
                'clinical_ai_summary_generated_at' => now(),
            ])->save();

            ClinicalAiAuditLog::query()->create([
                'patient_id' => $patient->getKey(),
                'user_id' => $this->requestedByUserId,
                'action' => 'patient_clinical_summary',
                'success' => true,
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            report($e);

            ClinicalAiAuditLog::query()->create([
                'patient_id' => $patient->getKey(),
                'user_id' => $this->requestedByUserId,
                'action' => 'patient_clinical_summary',
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
