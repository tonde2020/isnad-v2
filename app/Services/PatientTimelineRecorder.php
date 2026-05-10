<?php

namespace App\Services;

use App\Enums\PatientDiseaseKind;
use App\Enums\PatientMedicalEventType;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientDisease;
use App\Models\PatientMedicalEvent;
use App\Models\PatientMedication;
use Illuminate\Support\Carbon;

final class PatientTimelineRecorder
{
    public static function record(
        Patient $patient,
        PatientMedicalEventType $type,
        string $title,
        ?string $description = null,
        ?Carbon $eventDate = null,
        string $source = 'admin',
        array $metadata = [],
    ): PatientMedicalEvent {
        $now = Carbon::now();

        return PatientMedicalEvent::query()->create([
            'patient_id' => $patient->getKey(),
            'event_type' => $type,
            'event_date' => ($eventDate ?? $now)->toDateString(),
            'event_time' => $now->format('H:i:s'),
            'title' => $title,
            'description' => $description,
            'source' => $source,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    public static function diseaseAdded(PatientDisease $disease): void
    {
        $patient = $disease->patient;
        if (! $patient) {
            return;
        }

        $label = $disease->displayLabel();
        $type = $disease->kind === PatientDiseaseKind::Allergy
            ? PatientMedicalEventType::AllergyAdded
            : PatientMedicalEventType::DiagnosisChronic;

        $title = $type === PatientMedicalEventType::AllergyAdded
            ? 'تسجيل حساسية: '.$label
            : 'تسجيل مرض مزمن: '.$label;

        self::record(
            $patient,
            $type,
            $title,
            $disease->notes,
            $disease->diagnosed_at ? Carbon::parse($disease->diagnosed_at) : null,
            $disease->source ?? 'admin',
            [
                'patient_disease_id' => $disease->getKey(),
                'kind' => $disease->kind->value,
                'disease_master_id' => $disease->disease_master_id,
            ],
        );
    }

    public static function medicationStarted(PatientMedication $medication): void
    {
        $patient = $medication->patient;
        if (! $patient) {
            return;
        }

        self::record(
            $patient,
            PatientMedicalEventType::MedicationStarted,
            'بدء دواء: '.$medication->displayMedicationName(),
            $medication->instructions,
            $medication->start_date ? Carbon::parse($medication->start_date) : null,
            $medication->source ?? 'admin',
            [
                'patient_medication_id' => $medication->getKey(),
                'medication_master_id' => $medication->medication_master_id,
            ],
        );
    }

    public static function attachmentUploaded(MedicalRecord $record): void
    {
        $patient = $record->patient;
        if (! $patient) {
            return;
        }

        $docTitle = $record->title ?: 'مرفق طبي';

        $when = $record->uploaded_at
            ? Carbon::parse($record->uploaded_at)
            : ($record->record_date ? Carbon::parse($record->record_date) : null);

        self::record(
            $patient,
            PatientMedicalEventType::AttachmentUploaded,
            'رفع مرفق: '.$docTitle,
            $record->description,
            $when,
            'patient',
            [
                'medical_record_id' => $record->getKey(),
                'record_type' => $record->record_type,
            ],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $labRows
     */
    public static function labResultsFromExtraction(Patient $patient, MedicalRecord $record, array $labRows): void
    {
        if ($labRows === []) {
            return;
        }

        $lines = [];
        foreach ($labRows as $row) {
            $name = trim((string) ($row['test_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $value = trim((string) ($row['value'] ?? ''));
            $unit = trim((string) ($row['unit'] ?? ''));
            $ref = trim((string) ($row['reference_range'] ?? ''));
            $line = $name.': '.$value.($unit !== '' ? ' '.$unit : '');
            if ($ref !== '') {
                $line .= ' — مرجع: '.$ref;
            }
            $lines[] = $line;
        }

        if ($lines === []) {
            return;
        }

        self::record(
            $patient,
            PatientMedicalEventType::LabResultsRecorded,
            'ذكر نتائج مختبر (اعتماد استخراج)',
            implode("\n", $lines),
            null,
            'patient',
            [
                'medical_record_id' => $record->getKey(),
                'source' => 'extraction_review',
            ],
        );
    }
}
