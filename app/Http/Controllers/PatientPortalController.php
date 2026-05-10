<?php

namespace App\Http\Controllers;

use App\Enums\PatientDiseaseKind;
use App\Models\DiseaseMaster;
use App\Models\Patient;
use App\Models\PatientDisease;
use App\Support\PatientTemporaryProfileLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PatientPortalController extends Controller
{
    use AuthorizesRequests;

    public function editProfile(Request $request): View
    {
        $patient = $this->resolveOwnedPatientOrAbort($request);
        $this->authorize('updateOwnProfile', $patient);

        $patient->load([
            'patientChronicDiseases' => fn ($q) => $q->where('source', 'patient')->with('diseaseMaster'),
            'patientAllergyRecords' => fn ($q) => $q->where('source', 'patient')->with('diseaseMaster'),
        ]);

        $selectedChronicMasterIds = $patient->patientChronicDiseases
            ->pluck('disease_master_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $selectedAllergyMasterIds = $patient->patientAllergyRecords
            ->pluck('disease_master_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $chronicMasters = DiseaseMaster::query()
            ->where('is_active', true)
            ->where('category', '!=', 'allergy')
            ->orderBy('name_ar')
            ->get(['id', 'code', 'name_ar', 'name_en', 'category']);

        $quickPickOrder = [
            ['code' => 'hypertension', 'label' => 'ارتفاع ضغط الدم'],
            ['code' => 'diabetes_type_2', 'label' => 'السكري النوع الثاني'],
            ['code' => 'asthma', 'label' => 'الربو'],
            ['code' => 'chronic_kidney_disease', 'label' => 'مرض الكلى المزمن'],
            ['code' => 'chronic_anemia', 'label' => 'فقر الدم المزمن'],
            ['code' => 'heart_disease_general', 'label' => 'أمراض القلب'],
            ['code' => 'viral_hepatitis', 'label' => 'التهاب الكبد الوبائي'],
            ['code' => 'epilepsy', 'label' => 'الصرع'],
            ['code' => 'osteoarthritis', 'label' => 'خشونة المفاصل'],
            ['code' => 'chronic_allergy', 'label' => 'حساسية مزمنة', 'allergy' => true],
            ['code' => 'other_chronic_disease', 'label' => 'مرض مزمن آخر'],
        ];

        $quickPickCodes = collect($quickPickOrder)->pluck('code')->unique()->values()->all();
        $quickPickMasters = DiseaseMaster::query()
            ->where('is_active', true)
            ->whereIn('code', $quickPickCodes)
            ->get()
            ->keyBy('code');

        return view('patient.profile-edit', [
            'patient' => $patient,
            'user' => $request->user(),
            'chronicMasters' => $chronicMasters,
            'selectedChronicMasterIds' => $selectedChronicMasterIds,
            'selectedAllergyMasterIds' => $selectedAllergyMasterIds,
            'quickPickOrder' => $quickPickOrder,
            'quickPickMasters' => $quickPickMasters,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $patient = $this->resolveOwnedPatientOrAbort($request);
        $this->authorize('updateOwnProfile', $patient);

        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:64'],
            'national_id' => ['nullable', 'string', 'max:64'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'blood_type' => ['nullable', 'string', 'in:'.implode(',', $bloodTypes)],
            'gender' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:120'],
            'locality' => ['nullable', 'string', 'max:120'],
            'displacement_area' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:64'],
            'chronic_master_ids' => ['nullable', 'array'],
            'chronic_master_ids.*' => ['integer', 'exists:disease_masters,id'],
            'allergy_master_ids' => ['nullable', 'array'],
            'allergy_master_ids.*' => ['integer', 'exists:disease_masters,id'],
            'chronic_diseases' => ['nullable', 'string', 'max:5000'],
            'allergies' => ['nullable', 'string', 'max:5000'],
        ]);

        $chronicMasterIds = collect($validated['chronic_master_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $allergyMasterIds = collect($validated['allergy_master_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($chronicMasterIds->isNotEmpty()) {
            $badChronic = DiseaseMaster::query()
                ->whereIn('id', $chronicMasterIds->all())
                ->where('category', 'allergy')
                ->exists();
            if ($badChronic) {
                throw ValidationException::withMessages([
                    'chronic_master_ids' => ['اختيار غير صالح ضمن الأمراض المزمنة.'],
                ]);
            }
        }

        if ($allergyMasterIds->isNotEmpty()) {
            $badAllergy = DiseaseMaster::query()
                ->whereIn('id', $allergyMasterIds->all())
                ->where('category', '!=', 'allergy')
                ->exists();
            if ($badAllergy) {
                throw ValidationException::withMessages([
                    'allergy_master_ids' => ['اختيار غير صالح ضمن الحساسية.'],
                ]);
            }
        }

        DB::transaction(function () use ($patient, $chronicMasterIds, $allergyMasterIds, $validated): void {
            PatientDisease::query()
                ->where('patient_id', $patient->getKey())
                ->where('kind', PatientDiseaseKind::Chronic)
                ->where('source', 'patient')
                ->delete();

            foreach ($chronicMasterIds as $masterId) {
                PatientDisease::query()->create([
                    'patient_id' => $patient->getKey(),
                    'disease_master_id' => $masterId,
                    'kind' => PatientDiseaseKind::Chronic,
                    'custom_name' => null,
                    'status' => 'active',
                    'source' => 'patient',
                    'is_confirmed' => false,
                ]);
            }

            PatientDisease::query()
                ->where('patient_id', $patient->getKey())
                ->where('kind', PatientDiseaseKind::Allergy)
                ->where('source', 'patient')
                ->delete();

            foreach ($allergyMasterIds as $masterId) {
                PatientDisease::query()->create([
                    'patient_id' => $patient->getKey(),
                    'disease_master_id' => $masterId,
                    'kind' => PatientDiseaseKind::Allergy,
                    'custom_name' => null,
                    'status' => 'active',
                    'source' => 'patient',
                    'is_confirmed' => false,
                ]);
            }

            $patient->forceFill([
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'] ?: null,
                'birth_date' => $validated['birth_date'] ?: null,
                'blood_type' => $validated['blood_type'] ?: null,
                'gender' => $validated['gender'] ?: null,
                'state' => $validated['state'] ?: null,
                'locality' => $validated['locality'] ?: null,
                'displacement_area' => $validated['displacement_area'] ?: null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?: null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?: null,
                'chronic_diseases' => $validated['chronic_diseases'] ?: null,
                'allergies' => $validated['allergies'] ?: null,
            ])->save();
        });

        $request->user()->forceFill([
            'name' => $validated['name'],
        ])->save();

        return redirect()->to(filament()->getPanel('app')->getUrl() ?? url('/app'))
            ->with('status', 'تم حفظ بيانات ملفك.');
    }

    public function share(Request $request): View
    {
        $patient = $this->resolveOwnedPatientOrAbort($request);
        $this->authorize('generateTemporaryLink', $patient);

        return view('patient.share', [
            'patient' => $patient,
            'profileUrl' => PatientTemporaryProfileLink::publicProfileUrl($patient),
            'pdfUrl' => PatientTemporaryProfileLink::pdfUrl($patient),
            'expiryMinutes' => PatientTemporaryProfileLink::EXPIRY_MINUTES,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('patient.login');
    }

    private function resolveOwnedPatientOrAbort(Request $request): Patient
    {
        $patient = $request->user()->patients()->first();

        if ($patient === null) {
            abort(404);
        }

        return $patient;
    }
}
