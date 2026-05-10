<?php

namespace App\Support;

use App\Models\Patient;
use Illuminate\Support\Str;

/**
 * بيانات سريرية بدون معرفات مباشرة (لا اسم، لا هاتف، لا رقم وطني) لاستخدامها مع طلبات AI.
 */
final class PatientClinicalContextBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function forAiSummary(Patient $patient): array
    {
        $patient->loadMissing([
            'patientChronicDiseases.diseaseMaster',
            'patientAllergyRecords.diseaseMaster',
            'patientMedications' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
            'medications' => fn ($query) => $query->where('is_active', true)->orderBy('id'),
            'medicalRecords' => fn ($query) => $query
                ->orderByDesc('record_date')
                ->orderByDesc('id')
                ->limit(3),
        ]);

        $chronicStructured = $patient->patientChronicDiseases->map(fn ($row) => $row->displayLabel())->values()->all();

        $allergyStructured = $patient->patientAllergyRecords->map(fn ($row) => $row->displayLabel())->values()->all();

        if ($patient->patientMedications->isNotEmpty()) {
            $medicationsPayload = $patient->patientMedications->map(fn ($medication) => [
                'name' => $medication->displayMedicationName(),
                'dosage' => $medication->dosage,
                'frequency' => $medication->frequency,
                'duration' => $medication->duration,
            ])->values()->all();
        } else {
            $medicationsPayload = $patient->medications->map(fn ($medication) => [
                'name' => $medication->medication_name,
                'dosage' => $medication->dosage,
                'frequency' => $medication->frequency,
            ])->values()->all();
        }

        return [
            'patient_reference' => 'PT-'.substr((string) $patient->uuid, 0, 8),
            'age' => $patient->birth_date?->age,
            'blood_type' => $patient->blood_type,
            'chronic_conditions' => $chronicStructured,
            'chronic_free_text_note' => $patient->chronic_diseases,
            'allergies_structured' => $allergyStructured,
            'allergies_free_text_note' => $patient->allergies,
            'medications' => $medicationsPayload,
            'recent_documents' => $patient->medicalRecords->map(fn ($record) => [
                'title' => $record->title,
                'record_date' => $record->record_date?->format('Y-m-d'),
                'ocr_excerpt' => $record->ocr_text
                    ? Str::limit(preg_replace('/\s+/', ' ', (string) $record->ocr_text), 1200)
                    : null,
            ])->values()->all(),
        ];
    }
}
