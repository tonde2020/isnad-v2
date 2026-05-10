<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Patient || $this->hasClinicalPanelRole($user);
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->role === UserRole::Patient) {
            return $patient->user_id !== null
                && (int) $patient->user_id === (int) $user->getKey();
        }

        return $this->hasClinicalPanelRole($user);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Patient $patient): bool
    {
        if ($user->role === UserRole::Patient) {
            return $this->updateOwnProfile($user, $patient);
        }

        if ($user->role === UserRole::Admin) {
            return false;
        }

        return $user->role->canEditClinicalRecords();
    }

    public function delete(User $user, Patient $patient): bool
    {
        if ($user->role === UserRole::Admin) {
            return false;
        }

        return $user->role->canEditClinicalRecords();
    }

    public function deleteAny(User $user): bool
    {
        if ($user->role === UserRole::Admin) {
            return false;
        }

        return $user->role->canEditClinicalRecords();
    }

    /**
     * توليد رابط مؤقت للملف العام — الطاقم الطبي، أو المريض صاحب الحساب المرتبط بالملف فقط.
     */
    public function generateTemporaryLink(User $user, Patient $patient): bool
    {
        if ($user->role === UserRole::Patient && (int) $patient->user_id === (int) $user->getKey()) {
            return true;
        }

        if ($user->role === UserRole::Admin) {
            return false;
        }

        return $user->role->canEditClinicalRecords();
    }

    /**
     * تحديث بيانات الملف من بوابة المريض (حسابه المرتبط بنفس السجل).
     */
    public function updateOwnProfile(User $user, Patient $patient): bool
    {
        return $user->role === UserRole::Patient
            && $patient->user_id !== null
            && (int) $patient->user_id === (int) $user->getKey();
    }

    private function hasClinicalPanelRole(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
