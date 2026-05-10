<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientRegistrationController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if ($request->user()?->role === UserRole::Patient) {
            return redirect()->to(filament()->getPanel('app')->getUrl() ?? url('/app'));
        }

        return view('auth.patient-register');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()?->role === UserRole::Patient) {
            return redirect()->to(filament()->getPanel('app')->getUrl() ?? url('/app'));
        }

        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'accept_self_entered_disclaimer' => ['accepted'],
        ], [
            'accept_self_entered_disclaimer.accepted' => 'يجب الموافقة على إقرار المسؤولية عن صحة البيانات المدخلة.',
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => UserRole::Patient,
            ]);

            Patient::query()->create([
                'user_id' => $user->getKey(),
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
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to(filament()->getPanel('app')->getUrl() ?? url('/app'))
            ->with('status', 'تم إنشاء حسابك وملفك الأساسي. يمكنك تحديث البيانات لاحقاً عند توفر ذلك.');
    }
}
