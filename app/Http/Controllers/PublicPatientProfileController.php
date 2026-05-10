<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientAccessLog;
use App\Support\PatientTemporaryProfileLink;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicPatientProfileController extends Controller
{
    public function show(Request $request, string $patient): View
    {
        $model = $this->resolvePatient($patient);

        PatientAccessLog::create([
            'patient_id' => $model->id,
            'accessed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $expiresTs = $request->query('expires');

        return view('patients.public-profile', [
            'patient' => $model,
            'pdfDownloadUrl' => PatientTemporaryProfileLink::pdfUrl($model),
            'qrProfileUrl' => $request->fullUrl(),
            'linkExpiresAt' => is_numeric($expiresTs)
                ? Carbon::createFromTimestamp((int) $expiresTs)
                : null,
            'viaRegistryLookup' => false,
            'registryLookupExpiresAt' => null,
        ]);
    }

    public function downloadPdf(Request $request, string $patient): Response
    {
        $model = $this->resolvePatient($patient);

        PatientAccessLog::create([
            'patient_id' => $model->id,
            'accessed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $filename = 'patient-profile-'.$model->uuid.'.pdf';

        return Pdf::loadView('patients.public-profile-pdf', [
            'patient' => $model,
            'profileShareUrl' => PatientTemporaryProfileLink::publicProfileUrl($model),
        ])
            ->download($filename);
    }

    public function streamMedicalRecord(Request $request, string $patient, MedicalRecord $record): StreamedResponse
    {
        return $this->streamMedicalRecordInternal($request, $patient, $record, preferred: true);
    }

    public function streamMedicalRecordOriginal(Request $request, string $patient, MedicalRecord $record): StreamedResponse
    {
        return $this->streamMedicalRecordInternal($request, $patient, $record, preferred: false);
    }

    private function streamMedicalRecordInternal(
        Request $request,
        string $patient,
        MedicalRecord $record,
        bool $preferred,
    ): StreamedResponse {
        $patientModel = Patient::query()->where('uuid', $patient)->firstOrFail();

        abort_unless($record->patient_id === $patientModel->id, 404);

        PatientAccessLog::create([
            'patient_id' => $patientModel->id,
            'accessed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $loc = $preferred ? $record->preferredDisplayStorage() : $record->originalDisplayStorage();

        abort_if($loc['path'] === '', 404);
        abort_unless(Storage::disk($loc['disk'])->exists($loc['path']), 404);

        return Storage::disk($loc['disk'])->response($loc['path']);
    }

    private function resolvePatient(string $uuid): Patient
    {
        return Patient::query()
            ->where('uuid', $uuid)
            ->with(Patient::publicProfileWithRelations())
            ->firstOrFail();
    }
}
