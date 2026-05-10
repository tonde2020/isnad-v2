<?php

namespace App\Observers;

use App\Models\PatientDisease;
use App\Services\PatientTimelineRecorder;

class PatientDiseaseObserver
{
    public function saving(PatientDisease $patientDisease): void
    {
        if (! auth()->check()) {
            return;
        }

        if ($patientDisease->is_confirmed && $patientDisease->confirmed_by === null) {
            $patientDisease->confirmed_by = auth()->id();
            $patientDisease->confirmed_at = now();
        }
    }

    public function created(PatientDisease $patientDisease): void
    {
        PatientTimelineRecorder::diseaseAdded($patientDisease);
    }
}
