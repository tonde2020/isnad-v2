<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * إنشاء ملف المريض يتم من بوابة المريض ذاتياً؛ هذه الصفحة تعيد التوجيه فقط.
 */
class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    public function mount(): void
    {
        $this->redirect(route('patient.register'), navigate: false);
    }
}
