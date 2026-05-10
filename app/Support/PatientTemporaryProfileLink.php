<?php

namespace App\Support;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\URL;

final class PatientTemporaryProfileLink
{
    public const EXPIRY_MINUTES = 60;

    public static function publicProfileUrl(Patient $patient): string
    {
        return URL::temporarySignedRoute(
            'patients.public-profile',
            now()->addMinutes(self::EXPIRY_MINUTES),
            ['patient' => $patient->uuid],
        );
    }

    public static function pdfUrl(Patient $patient): string
    {
        return URL::temporarySignedRoute(
            'patients.public-profile.pdf',
            now()->addMinutes(self::EXPIRY_MINUTES),
            ['patient' => $patient->uuid],
        );
    }

    public static function medicalRecordFileUrl(Patient $patient, MedicalRecord $record): string
    {
        return URL::temporarySignedRoute(
            'patients.public-record-file',
            now()->addMinutes(self::EXPIRY_MINUTES),
            ['patient' => $patient->uuid, 'record' => $record->getKey()],
        );
    }

    public static function medicalRecordOriginalFileUrl(Patient $patient, MedicalRecord $record): string
    {
        return URL::temporarySignedRoute(
            'patients.public-record-file-original',
            now()->addMinutes(self::EXPIRY_MINUTES),
            ['patient' => $patient->uuid, 'record' => $record->getKey()],
        );
    }

    public static function whatsappShareUrl(string $profileUrl): string
    {
        $message = 'رابط ملف المريض (موقّع، صالح لمدة '.self::EXPIRY_MINUTES." دقيقة):\n\n".$profileUrl;

        return 'https://wa.me/?text='.rawurlencode($message);
    }
}
