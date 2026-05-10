<?php

namespace App\Services;

use App\Enums\PatientDiseaseKind;
use App\Models\DiseaseMaster;
use App\Models\HealthIndicatorSnapshot;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientDisease;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class HealthIndicatorSnapshotGenerator
{
    public function generateNationalDaily(?CarbonInterface $forDate = null): HealthIndicatorSnapshot
    {
        $date = $forDate ? Carbon::parse($forDate->format('Y-m-d')) : Carbon::today();
        $min = max(1, (int) config('isnad.health_snapshots.minimum_group_size', 10));

        $totalPatients = Patient::query()->count();

        $rawDisease = PatientDisease::query()
            ->where('kind', PatientDiseaseKind::Chronic)
            ->where('status', 'active')
            ->whereNotNull('disease_master_id')
            ->selectRaw('disease_master_id, count(distinct patient_id) as patient_count')
            ->groupBy('disease_master_id')
            ->get();

        $diseaseLabels = DiseaseMaster::query()
            ->whereIn('id', $rawDisease->pluck('disease_master_id'))
            ->pluck('name_ar', 'id');

        $diseasesIncluded = [];
        $diseaseGroupsSuppressed = 0;

        foreach ($rawDisease as $row) {
            $count = (int) $row->patient_count;
            if ($count < $min) {
                $diseaseGroupsSuppressed++;

                continue;
            }

            $id = (int) $row->disease_master_id;
            $diseasesIncluded[] = [
                'disease_master_id' => $id,
                'label' => (string) ($diseaseLabels[$id] ?? ('#'.$id)),
                'distinct_patients' => $count,
                'percentage_of_patients' => $totalPatients > 0
                    ? round($count / $totalPatients * 100, 2)
                    : 0.0,
            ];
        }

        usort($diseasesIncluded, fn (array $a, array $b): int => $b['distinct_patients'] <=> $a['distinct_patients']);

        $ageBuckets = ['0-5' => 0, '6-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];
        Patient::query()->whereNotNull('birth_date')->chunkById(500, function ($patients) use (&$ageBuckets): void {
            foreach ($patients as $patient) {
                $age = $patient->birth_date->age;
                if ($age <= 5) {
                    $ageBuckets['0-5']++;
                } elseif ($age <= 17) {
                    $ageBuckets['6-17']++;
                } elseif ($age <= 35) {
                    $ageBuckets['18-35']++;
                } elseif ($age <= 59) {
                    $ageBuckets['36-59']++;
                } else {
                    $ageBuckets['60+']++;
                }
            }
        });

        $ageGroups = [];
        $ageGroupsSuppressed = 0;
        foreach ($ageBuckets as $bucket => $count) {
            if ($count < $min) {
                $ageGroupsSuppressed++;

                continue;
            }
            $ageGroups[] = ['bucket' => $bucket, 'count' => $count];
        }

        $genderRaw = Patient::query()
            ->selectRaw('gender, count(*) as c')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->pluck('c', 'gender')
            ->all();

        $genderCounts = [];
        $genderGroupsSuppressed = 0;
        foreach ($genderRaw as $gender => $count) {
            $c = (int) $count;
            if ($c < $min) {
                $genderGroupsSuppressed++;

                continue;
            }
            $genderCounts[] = ['gender' => (string) $gender, 'count' => $c];
        }

        $recordTypeRaw = MedicalRecord::query()
            ->selectRaw('record_type, count(*) as c')
            ->whereNotNull('record_type')
            ->groupBy('record_type')
            ->pluck('c', 'record_type')
            ->all();

        $recordTypes = [];
        $recordTypeGroupsSuppressed = 0;
        foreach ($recordTypeRaw as $type => $count) {
            $c = (int) $count;
            if ($c < $min) {
                $recordTypeGroupsSuppressed++;

                continue;
            }
            $recordTypes[] = ['record_type' => (string) $type, 'count' => $c];
        }

        $distinctChronicPatients = (int) (PatientDisease::query()
            ->where('kind', PatientDiseaseKind::Chronic)
            ->where('status', 'active')
            ->selectRaw('count(distinct patient_id) as aggregate')
            ->value('aggregate') ?? 0);

        $payload = [
            'meta' => [
                'minimum_group_size' => $min,
                'generated_at' => now()->toIso8601String(),
                'total_patients' => $totalPatients,
                'distinct_patients_with_active_chronic' => $distinctChronicPatients,
                'medical_records_total' => MedicalRecord::query()->count(),
                'disease_groups_suppressed' => $diseaseGroupsSuppressed,
                'age_bucket_groups_suppressed' => $ageGroupsSuppressed,
                'gender_groups_suppressed' => $genderGroupsSuppressed,
                'record_type_groups_suppressed' => $recordTypeGroupsSuppressed,
            ],
            'diseases' => $diseasesIncluded,
            'age_groups' => $ageGroups,
            'gender_counts' => $genderCounts,
            'record_types' => $recordTypes,
        ];

        return HealthIndicatorSnapshot::query()->updateOrCreate(
            [
                'snapshot_date' => $date->toDateString(),
                'region_key' => 'national',
            ],
            ['payload' => $payload],
        );
    }
}
