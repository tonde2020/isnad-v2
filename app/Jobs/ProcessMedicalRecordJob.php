<?php

namespace App\Jobs;

use App\Models\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessMedicalRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $medicalRecordId,
    ) {}

    public function handle(): void
    {
        $record = MedicalRecord::query()->find($this->medicalRecordId);

        if ($record === null || $record->file_path === null || $record->file_path === '') {
            return;
        }

        $record->update(['processing_status' => 'processing']);

        try {
            [$sourceDisk, $absolutePath, $mime] = $this->resolveSource($record);

            if ($sourceDisk === null || $absolutePath === null || ! is_file($absolutePath)) {
                $record->update([
                    'processing_status' => 'failed',
                    'processed_at' => now(),
                ]);

                return;
            }

            $binary = Storage::disk($sourceDisk)->get($record->file_path);
            $enhancedRelative = 'medical-records/enhanced/'.$record->getKey().'_'.basename($record->file_path);

            $enhancedBinary = $this->enhanceBinary($binary, $mime);

            Storage::disk('medical_private')->put($enhancedRelative, $enhancedBinary);

            $ocrText = $this->maybeRunTesseract($absolutePath, $mime);

            $record->update([
                'enhanced_file_path' => $enhancedRelative,
                'ocr_text' => $ocrText ?: $record->ocr_text,
                'processing_status' => 'completed',
                'processed_at' => now(),
            ]);

            $record->refresh();

            if (filled($record->ocr_text) && config('isnad.documents.extraction_enabled')) {
                ExtractMedicalEntitiesFromOcrJob::dispatch($record->getKey())->afterCommit();
            }
        } catch (Throwable $e) {
            report($e);

            $record->update([
                'processing_status' => 'failed',
                'processed_at' => now(),
            ]);
        }
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    private function resolveSource(MedicalRecord $record): array
    {
        foreach (['medical_private', 'public'] as $diskName) {
            if (! Storage::disk($diskName)->exists($record->file_path)) {
                continue;
            }

            $path = Storage::disk($diskName)->path($record->file_path);

            $mime = is_file($path) ? $this->resolveMimeType($path) : null;

            return [$diskName, $path, $mime];
        }

        return [null, null, null];
    }

    private function enhanceBinary(string $binary, ?string $mime): string
    {
        if ($mime !== null && str_starts_with($mime, 'image/') && extension_loaded('imagick')) {
            try {
                $image = new \Imagick;
                $image->readImageBlob($binary);
                $image->setImageColorspace(\Imagick::COLORSPACE_RGB);
                $image->normalizeImage();
                $image->enhanceImage();
                $image->setImageFormat($image->getImageFormat() ?: 'PNG');

                return $image->getImageBlob();
            } catch (Throwable) {
                // fallback to original bytes
            }
        }

        return $binary;
    }

    private function maybeRunTesseract(string $absolutePath, ?string $mime): ?string
    {
        $binary = config('isnad.documents.tesseract_binary');

        if (! is_string($binary) || $binary === '') {
            return null;
        }

        if ($mime === null || ! str_starts_with($mime, 'image/')) {
            return null;
        }

        try {
            foreach (['ara+eng', 'eng'] as $lang) {
                $result = Process::timeout(120)->run([
                    $binary,
                    $absolutePath,
                    'stdout',
                    '-l',
                    $lang,
                ]);

                if ($result->successful()) {
                    $text = trim($result->output());

                    if ($text !== '') {
                        return $text;
                    }

                    continue;
                }

                Log::warning('Tesseract OCR attempt failed', [
                    'medical_record_id' => $this->medicalRecordId,
                    'lang' => $lang,
                    'exit_code' => $result->exitCode(),
                    'stderr' => Str::limit($result->errorOutput(), 2000),
                ]);
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Tesseract OCR threw', [
                'medical_record_id' => $this->medicalRecordId,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * على Windows غالباً يُعاد application/octet-stream رغم أن الملف صورة، فيُمنع OCR بالخطأ.
     */
    private function resolveMimeType(string $path): ?string
    {
        $detected = @mime_content_type($path);

        if (is_string($detected) && $detected !== '' && $detected !== 'application/octet-stream') {
            if (str_starts_with($detected, 'image/')) {
                return $detected;
            }
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        $fromExtension = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'tif', 'tiff' => 'image/tiff',
            default => null,
        };

        if ($fromExtension !== null) {
            return $fromExtension;
        }

        return $this->sniffImageMimeFromPath($path);
    }

    private function sniffImageMimeFromPath(string $path): ?string
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            $header = fread($handle, 16);
            if ($header === false || $header === '') {
                return null;
            }

            if (str_starts_with($header, "\xFF\xD8\xFF")) {
                return 'image/jpeg';
            }

            if (str_starts_with($header, "\x89PNG\r\n\x1a\n")) {
                return 'image/png';
            }

            if (str_starts_with($header, 'GIF87a') || str_starts_with($header, 'GIF89a')) {
                return 'image/gif';
            }

            if (strlen($header) >= 12 && substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') {
                return 'image/webp';
            }

            return null;
        } finally {
            fclose($handle);
        }
    }
}
