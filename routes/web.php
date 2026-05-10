<?php

use App\Http\Controllers\MedicalPrivateFileController;
use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PatientRegistrationController;
use App\Http\Controllers\PatientRegistryLookupController;
use App\Http\Controllers\PublicPatientProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/component-guide', 'component-guide')->name('component-guide');

Route::get('/login', fn () => redirect()->route('patient.login'))->name('login');

Route::get('/patient/lookup', [PatientRegistryLookupController::class, 'create'])
    ->name('patient.registry.lookup');
Route::post('/patient/lookup', [PatientRegistryLookupController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('patient.registry.lookup.submit');
Route::get('/patient/lookup/view', [PatientRegistryLookupController::class, 'show'])
    ->name('patient.registry.lookup.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/register/patient', [PatientRegistrationController::class, 'create'])
        ->name('patient.register');
    Route::post('/register/patient', [PatientRegistrationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('patient.register.store');

    Route::get('/login/patient', [PatientAuthController::class, 'create'])
        ->name('patient.login');
    Route::post('/login/patient', [PatientAuthController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('patient.login.store');
});

Route::middleware(['auth', 'patient.portal'])->prefix('patient')->name('patient.')->group(function (): void {
    Route::get('/', function () {
        return redirect()->to(filament()->getPanel('app')->getUrl() ?? url('/app'));
    })->name('home');
    Route::get('/profile', [PatientPortalController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [PatientPortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/share', [PatientPortalController::class, 'share'])->name('share');
    Route::post('/logout', [PatientPortalController::class, 'logout'])->name('logout');
});

Route::get('/p/{patient}', [PublicPatientProfileController::class, 'show'])
    ->name('patients.public-profile')
    ->middleware('signed');

Route::get('/p/{patient}/pdf', [PublicPatientProfileController::class, 'downloadPdf'])
    ->name('patients.public-profile.pdf')
    ->middleware('signed');

Route::get('/p/{patient}/records/{record}/file', [PublicPatientProfileController::class, 'streamMedicalRecord'])
    ->name('patients.public-record-file')
    ->middleware('signed');

Route::get('/p/{patient}/records/{record}/file/original', [PublicPatientProfileController::class, 'streamMedicalRecordOriginal'])
    ->name('patients.public-record-file-original')
    ->middleware('signed');

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/medical-private-files/stream', [MedicalPrivateFileController::class, 'stream'])
        ->middleware('signed')
        ->name('medical-private-files.stream');
});
