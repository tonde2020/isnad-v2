<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PatientMedicalEvent;
use App\Models\User;

class PatientMedicalEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewSensitiveClinicalData()
            || $user->role === UserRole::Patient;
    }

    public function view(User $user, PatientMedicalEvent $patientMedicalEvent): bool
    {
        if ($user->role === UserRole::Patient) {
            $patient = $patientMedicalEvent->patient;

            return $patient !== null
                && $patient->user_id !== null
                && (int) $patient->user_id === (int) $user->getKey();
        }

        return $user->canViewSensitiveClinicalData();
    }
}
