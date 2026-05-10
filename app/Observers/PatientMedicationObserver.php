<?php

namespace App\Observers;

use App\Models\PatientMedication;
use App\Services\PatientTimelineRecorder;

class PatientMedicationObserver
{
    public function saving(PatientMedication $patientMedication): void
    {
        if (! auth()->check()) {
            return;
        }

        if ($patientMedication->is_confirmed && $patientMedication->confirmed_by === null) {
            $patientMedication->confirmed_by = auth()->id();
            $patientMedication->confirmed_at = now();
        }
    }

    public function created(PatientMedication $patientMedication): void
    {
        PatientTimelineRecorder::medicationStarted($patientMedication);
    }
}
