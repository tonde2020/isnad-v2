<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PatientAuthController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if ($request->user()?->role === UserRole::Patient) {
            return redirect()->to(filament()->getPanel('app')->getUrl() ?? url('/app'));
        }

        return view('auth.patient-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], remember: $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user === null || $user->role !== UserRole::Patient) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'هذا الدخول مخصص لحساب حامل الملف. فريق الرعاية يستخدم صفحة الدخول على مسار /app/login.',
            ]);
        }

        return redirect()->intended(filament()->getPanel('app')->getUrl() ?? url('/app'));
    }
}
