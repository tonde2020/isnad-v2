<?php

namespace App\Support;

use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Facades\URL;

final class MedicalPrivateStorageUrl
{
    /**
     * رابط موقّع نسبي (نفس منفذ الطلب) لعرض/تنزيل ملف من قرص medical_private.
     */
    public static function signedStreamUrl(string $relativePath): string
    {
        $ttl = (int) config('filament.temporary_file_url_expiry_minutes', 30);

        return URL::temporarySignedRoute(
            'medical-private-files.stream',
            now()->addMinutes(max(1, $ttl)),
            ['path' => $relativePath],
            absolute: true,
        );
    }

    /**
     * بيانات ملف لـ Filament FileUpload بدل Storage::url الذي يولّد /storage/... الخاطئ للقرص الخاص.
     *
     * @param  string|array<string, string>|null  $storedFileNames
     * @return array{name: string, size: int, type: string|null, url: string}|null
     */
    public static function filamentUploadedFilePayload(
        BaseFileUpload $component,
        string $file,
        string|array|null $storedFileNames,
    ): ?array {
        $storage = $component->getDisk();
        $shouldFetchFileInformation = $component->shouldFetchFileInformation();

        if ($shouldFetchFileInformation) {
            try {
                if (! $storage->exists($file)) {
                    return null;
                }
            } catch (\Throwable) {
                return null;
            }
        }

        $name = ($component->isMultiple()
            ? ($storedFileNames[$file] ?? null)
            : $storedFileNames) ?? basename($file);

        return [
            'name' => $name,
            'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
            'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
            'url' => self::signedStreamUrl($file),
        ];
    }
}
