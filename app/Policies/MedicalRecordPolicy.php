<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewSensitiveClinicalData()
            || $user->role === UserRole::Patient;
    }

    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user->role === UserRole::Patient) {
            return $this->patientOwnsMedicalRecord($user, $medicalRecord);
        }

        return $user->canViewSensitiveClinicalData();
    }

    public function create(User $user): bool
    {
        return $user->role->canEditClinicalRecords()
            || $user->role === UserRole::Patient;
    }

    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user->role === UserRole::Patient) {
            return $this->patientOwnsMedicalRecord($user, $medicalRecord);
        }

        return $user->role->canEditClinicalRecords();
    }

    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user->role === UserRole::Patient) {
            return $this->patientOwnsMedicalRecord($user, $medicalRecord);
        }

        return $user->role->canEditClinicalRecords();
    }

    /**
     * اعتماد المستخرج أو حفظ مسودة المراجعة — للمريض على مرفقات ملفه فقط، وقبل أول اعتماد.
     */
    public function manageMedicalRecordExtraction(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($medicalRecord->extracted_entities_applied_at !== null) {
            return false;
        }

        return $user->role === UserRole::Patient
            && $this->patientOwnsMedicalRecord($user, $medicalRecord);
    }

    private function patientOwnsMedicalRecord(User $user, MedicalRecord $medicalRecord): bool
    {
        $patient = $medicalRecord->patient;

        return $patient !== null
            && $patient->user_id !== null
            && (int) $patient->user_id === (int) $user->getKey();
    }
}
