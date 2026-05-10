<?php

namespace App\Services;

use App\Enums\PatientDiseaseKind;
use App\Models\DiseaseMaster;
use App\Models\MedicalRecord;
use App\Models\MedicationMaster;
use App\Models\PatientDisease;
use App\Models\PatientMedication;
use Illuminate\Support\Facades\DB;

final class ApplyMedicalRecordExtractedEntities
{
    /**
     * @param  array{
     *     conditions?: list<int|string>,
     *     allergies?: list<int|string>,
     *     medications?: list<int|string>,
     *     lab_indices?: list<int|string>,
     *     condition_name_fixes?: array<string, string>,
     *     allergy_name_fixes?: array<string, string>,
     *     medication_name_fixes?: array<string, string>,
     * }  $selection
     * @return array{diseases_created: int, medications_created: int, skipped_duplicates: int, lab_timeline_events: int}
     */
    public function apply(MedicalRecord $record, array $selection, ?int $reviewerUserId): array
    {
        if ($record->extracted_entities_applied_at !== null) {
            return ['diseases_created' => 0, 'medications_created' => 0, 'skipped_duplicates' => 0, 'lab_timeline_events' => 0];
        }

        $entities = $record->extracted_entities;

        if (! is_array($entities) || $entities === []) {
            return ['diseases_created' => 0, 'medications_created' => 0, 'skipped_duplicates' => 0, 'lab_timeline_events' => 0];
        }

        $patient = $record->patient;
        if ($patient === null) {
            return ['diseases_created' => 0, 'medications_created' => 0, 'skipped_duplicates' => 0, 'lab_timeline_events' => 0];
        }

        $conditions = $entities['conditions'] ?? [];
        $allergies = $entities['allergies'] ?? [];
        $medications = $entities['medications'] ?? [];
        $labs = $entities['lab_results'] ?? [];

        $conditionIndices = array_map('intval', $selection['conditions'] ?? []);
        $allergyIndices = array_map('intval', $selection['allergies'] ?? []);
        $medicationIndices = array_map('intval', $selection['medications'] ?? []);
        $labIndices = array_map('intval', $selection['lab_indices'] ?? []);

        $conditionFixes = $this->normalizeStringKeyMap($selection['condition_name_fixes'] ?? []);
        $allergyFixes = $this->normalizeStringKeyMap($selection['allergy_name_fixes'] ?? []);
        $medicationFixes = $this->normalizeStringKeyMap($selection['medication_name_fixes'] ?? []);

        $stats = ['diseases_created' => 0, 'medications_created' => 0, 'skipped_duplicates' => 0, 'lab_timeline_events' => 0];

        DB::transaction(function () use (
            $record,
            $patient,
            $conditions,
            $allergies,
            $medications,
            $labs,
            $conditionIndices,
            $allergyIndices,
            $medicationIndices,
            $labIndices,
            $conditionFixes,
            $allergyFixes,
            $medicationFixes,
            $reviewerUserId,
            &$stats,
        ): void {
            foreach ($conditionIndices as $i) {
                if (! isset($conditions[$i]) || ! is_array($conditions[$i])) {
                    continue;
                }
                $rawName = trim((string) ($conditions[$i]['name'] ?? ''));
                $name = $this->resolvedLabel($conditionFixes, $i, $rawName);
                if ($name === '') {
                    continue;
                }

                $masterId = $this->resolveDiseaseMasterId($name, 'chronic');
                if ($masterId !== null && $this->patientHasActiveChronicMaster($patient->getKey(), $masterId)) {
                    $stats['skipped_duplicates']++;

                    continue;
                }

                PatientDisease::query()->create([
                    'patient_id' => $patient->getKey(),
                    'disease_master_id' => $masterId,
                    'kind' => PatientDiseaseKind::Chronic,
                    'custom_name' => $masterId === null ? $name : null,
                    'status' => 'active',
                    'source' => 'import',
                    'is_confirmed' => true,
                    'confirmed_by' => $reviewerUserId,
                    'confirmed_at' => now(),
                    'notes' => 'مستورد من استخراج مستند #'.$record->getKey(),
                ]);
                $stats['diseases_created']++;
            }

            foreach ($allergyIndices as $i) {
                if (! isset($allergies[$i]) || ! is_array($allergies[$i])) {
                    continue;
                }
                $rawName = trim((string) ($allergies[$i]['name'] ?? ''));
                $name = $this->resolvedLabel($allergyFixes, $i, $rawName);
                if ($name === '') {
                    continue;
                }

                $masterId = $this->resolveDiseaseMasterId($name, 'allergy');
                if ($masterId !== null && $this->patientHasActiveAllergyMaster($patient->getKey(), $masterId)) {
                    $stats['skipped_duplicates']++;

                    continue;
                }

                PatientDisease::query()->create([
                    'patient_id' => $patient->getKey(),
                    'disease_master_id' => $masterId,
                    'kind' => PatientDiseaseKind::Allergy,
                    'custom_name' => $masterId === null ? $name : null,
                    'status' => 'active',
                    'source' => 'import',
                    'is_confirmed' => true,
                    'confirmed_by' => $reviewerUserId,
                    'confirmed_at' => now(),
                    'notes' => 'مستورد من استخراج مستند #'.$record->getKey(),
                ]);
                $stats['diseases_created']++;
            }

            foreach ($medicationIndices as $i) {
                if (! isset($medications[$i]) || ! is_array($medications[$i])) {
                    continue;
                }
                $m = $medications[$i];
                $rawName = trim((string) ($m['name'] ?? ''));
                $name = $this->resolvedLabel($medicationFixes, $i, $rawName);
                if ($name === '') {
                    continue;
                }

                $masterId = $this->resolveMedicationMasterId($name);
                if ($masterId !== null && $this->patientHasActiveMedicationMaster($patient->getKey(), $masterId)) {
                    $stats['skipped_duplicates']++;

                    continue;
                }

                PatientMedication::query()->create([
                    'patient_id' => $patient->getKey(),
                    'medication_master_id' => $masterId,
                    'custom_medication_name' => $masterId === null ? $name : null,
                    'dosage' => isset($m['dosage']) ? (string) $m['dosage'] : null,
                    'frequency' => isset($m['frequency']) ? (string) $m['frequency'] : null,
                    'duration' => isset($m['duration']) ? (string) $m['duration'] : null,
                    'instructions' => isset($m['instructions']) ? (string) $m['instructions'] : null,
                    'start_date' => null,
                    'source' => 'import',
                    'is_confirmed' => true,
                    'confirmed_by' => $reviewerUserId,
                    'confirmed_at' => now(),
                    'is_active' => true,
                ]);
                $stats['medications_created']++;
            }

            $labRowsSelected = [];
            foreach ($labIndices as $i) {
                if (! isset($labs[$i]) || ! is_array($labs[$i])) {
                    continue;
                }
                $labRowsSelected[] = $labs[$i];
            }

            if ($labRowsSelected !== []) {
                PatientTimelineRecorder::labResultsFromExtraction($patient, $record, $labRowsSelected);
                $stats['lab_timeline_events'] = 1;
            }

            $record->forceFill([
                'extracted_entities_applied_at' => now(),
                'extracted_entities_applied_by' => $reviewerUserId,
                'extraction_review_draft' => null,
                'is_reviewed' => true,
                'reviewed_by' => $reviewerUserId,
                'reviewed_at' => now(),
            ])->save();
        });

        return $stats;
    }

    /**
     * @param  array<string, string>  $fixes
     */
    private function resolvedLabel(array $fixes, int $index, string $fallback): string
    {
        $key = (string) $index;

        if (isset($fixes[$key]) && $fixes[$key] !== '') {
            return $fixes[$key];
        }

        return trim($fallback);
    }

    /**
     * @return array<string, string>
     */
    private function normalizeStringKeyMap(mixed $fixes): array
    {
        if (! is_array($fixes)) {
            return [];
        }

        $out = [];
        foreach ($fixes as $k => $v) {
            $out[(string) $k] = trim((string) $v);
        }

        return $out;
    }

    private function resolveDiseaseMasterId(string $name, string $category): ?int
    {
        $query = DiseaseMaster::query()
            ->where('is_active', true)
            ->where(function ($q) use ($name): void {
                $q->where('name_ar', 'like', '%'.$name.'%')
                    ->orWhere('name_en', 'like', '%'.$name.'%');
            });

        if ($category === 'allergy') {
            $query->where('category', 'allergy');
        } else {
            $query->where('category', '!=', 'allergy');
        }

        $master = $query->orderBy('id')->first(['id']);

        return $master?->getKey();
    }

    private function resolveMedicationMasterId(string $name): ?int
    {
        $medication = MedicationMaster::query()
            ->where('is_active', true)
            ->where(function ($q) use ($name): void {
                $q->where('brand_name', 'like', '%'.$name.'%')
                    ->orWhere('generic_name', 'like', '%'.$name.'%');
            })
            ->orderBy('id')
            ->first(['id']);

        return $medication?->getKey();
    }

    private function patientHasActiveChronicMaster(int $patientId, int $diseaseMasterId): bool
    {
        return PatientDisease::query()
            ->where('patient_id', $patientId)
            ->where('kind', PatientDiseaseKind::Chronic)
            ->where('status', 'active')
            ->where('disease_master_id', $diseaseMasterId)
            ->exists();
    }

    private function patientHasActiveAllergyMaster(int $patientId, int $diseaseMasterId): bool
    {
        return PatientDisease::query()
            ->where('patient_id', $patientId)
            ->where('kind', PatientDiseaseKind::Allergy)
            ->where('status', 'active')
            ->where('disease_master_id', $diseaseMasterId)
            ->exists();
    }

    private function patientHasActiveMedicationMaster(int $patientId, int $medicationMasterId): bool
    {
        return PatientMedication::query()
            ->where('patient_id', $patientId)
            ->where('medication_master_id', $medicationMasterId)
            ->where('is_active', true)
            ->exists();
    }
}
