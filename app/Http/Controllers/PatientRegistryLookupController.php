<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientAccessLog;
use App\Support\PatientTemporaryProfileLink;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientRegistryLookupController extends Controller
{
    private const SESSION_KEY = 'patient_registry_lookup';

    private const CAPTCHA_SESSION_KEY = 'registry_lookup_captcha';

    public function create(Request $request): View
    {
        $a = random_int(2, 12);
        $b = random_int(2, 12);
        $request->session()->put(self::CAPTCHA_SESSION_KEY, [
            'answer' => $a + $b,
            'expires_at' => now()->addMinutes(15)->getTimestamp(),
        ]);

        return view('patient.registry-lookup', [
            'status' => $request->session()->get('status'),
            'captchaA' => $a,
            'captchaB' => $b,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (filled($request->string('website')->toString())) {
            return redirect()
                ->route('patient.registry.lookup')
                ->withErrors(['registry_number' => 'تعذّر إتمام الطلب. حاول مجدداً.']);
        }

        $validated = $request->validate([
            'registry_number' => ['required', 'string', 'max:48'],
            'captcha_answer' => ['required', 'integer'],
        ], [], [
            'registry_number' => 'رقم السجل',
            'captcha_answer' => 'إجابة التحقق',
        ]);

        $captchaPayload = $request->session()->get(self::CAPTCHA_SESSION_KEY);
        $request->session()->forget(self::CAPTCHA_SESSION_KEY);

        if (! is_array($captchaPayload) || ! isset($captchaPayload['answer'], $captchaPayload['expires_at'])) {
            return redirect()
                ->route('patient.registry.lookup')
                ->withErrors(['captcha_answer' => 'انتهت صلاحية التحقق. حدّث الصفحة وحاول مجدداً.'])
                ->withInput($request->only('registry_number'));
        }

        if (now()->getTimestamp() > (int) $captchaPayload['expires_at']) {
            return redirect()
                ->route('patient.registry.lookup')
                ->withErrors(['captcha_answer' => 'انتهت صلاحية التحقق. حاول مجدداً.'])
                ->withInput($request->only('registry_number'));
        }

        if ((int) $validated['captcha_answer'] !== (int) $captchaPayload['answer']) {
            return redirect()
                ->route('patient.registry.lookup')
                ->withErrors(['captcha_answer' => 'إجابة التحقق غير صحيحة.'])
                ->withInput($request->only('registry_number'));
        }

        $normalized = Patient::normalizeRegistryNumberInput($validated['registry_number']);

        $patient = Patient::query()
            ->where('registry_number', $normalized)
            ->first();

        if ($patient === null) {
            return redirect()
                ->route('patient.registry.lookup')
                ->withErrors(['registry_number' => 'رقم السجل غير موجود. تحقق من الكتابة والمسافات.'])
                ->withInput($request->only('registry_number'));
        }

        PatientAccessLog::create([
            'patient_id' => $patient->id,
            'accessed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $ttl = (int) config('isnad.registry.lookup_session_ttl_minutes', 15);

        $request->session()->put(self::SESSION_KEY, [
            'patient_id' => $patient->getKey(),
            'expires_at' => now()->addMinutes($ttl)->getTimestamp(),
        ]);

        return redirect()->route('patient.registry.lookup.show');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $payload = $request->session()->get(self::SESSION_KEY);

        if (! is_array($payload) || ! isset($payload['patient_id'], $payload['expires_at'])) {
            return redirect()
                ->route('patient.registry.lookup')
                ->with('status', 'أدخل رقم السجل للاطلاع على الملف (عرض فقط).');
        }

        if (now()->getTimestamp() > (int) $payload['expires_at']) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()
                ->route('patient.registry.lookup')
                ->withErrors(['registry_number' => 'انتهت مهلة عرض الملف. أدخل رقم السجل مجدداً.']);
        }

        $patient = Patient::query()
            ->whereKey((int) $payload['patient_id'])
            ->with(Patient::publicProfileWithRelations())
            ->firstOrFail();

        return view('patients.public-profile', [
            'patient' => $patient,
            'pdfDownloadUrl' => PatientTemporaryProfileLink::pdfUrl($patient),
            'qrProfileUrl' => $request->fullUrl(),
            'linkExpiresAt' => null,
            'viaRegistryLookup' => true,
            'registryLookupExpiresAt' => Carbon::createFromTimestamp((int) $payload['expires_at']),
        ]);
    }
}
