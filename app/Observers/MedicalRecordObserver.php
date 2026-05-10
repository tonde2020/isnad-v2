<?php

namespace App\Observers;

use App\Jobs\ProcessMedicalRecordJob;
use App\Models\MedicalRecord;
use App\Services\PatientTimelineRecorder;

class MedicalRecordObserver
{
    public function saving(MedicalRecord $medicalRecord): void
    {
        if (! auth()->check()) {
            return;
        }

        if ($medicalRecord->is_reviewed && $medicalRecord->reviewed_by === null) {
            $medicalRecord->reviewed_by = auth()->id();
            $medicalRecord->reviewed_at = now();
        }
    }

    public function created(MedicalRecord $medicalRecord): void
    {
        if ($medicalRecord->patient_id) {
            PatientTimelineRecorder::attachmentUploaded($medicalRecord);
        }
    }

    public function saved(MedicalRecord $medicalRecord): void
    {
        if ($medicalRecord->file_path === null || $medicalRecord->file_path === '') {
            return;
        }

        if ($medicalRecord->wasRecentlyCreated || $medicalRecord->wasChanged('file_path')) {
            ProcessMedicalRecordJob::dispatch($medicalRecord->getKey())->afterCommit();
        }
    }
}
