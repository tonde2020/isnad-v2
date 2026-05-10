<?php

namespace App\Providers;

use App\Contracts\ClinicalSummarizer;
use App\Contracts\MedicalDocumentEntityExtractor;
use App\Listeners\RecordUserFirstLogin;
use App\Models\MedicalRecord;
use App\Models\PatientDisease;
use App\Models\PatientMedication;
use App\Observers\MedicalRecordObserver;
use App\Observers\PatientDiseaseObserver;
use App\Observers\PatientMedicationObserver;
use App\Services\ClinicalAi\NullClinicalSummarizer;
use App\Services\ClinicalAi\NullMedicalDocumentEntityExtractor;
use App\Services\ClinicalAi\OpenAiClinicalSummarizer;
use App\Services\ClinicalAi\OpenAiMedicalDocumentEntityExtractor;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClinicalSummarizer::class, function (): ClinicalSummarizer {
            $enabled = (bool) config('isnad.ai.enabled', false);
            $key = config('isnad.ai.api_key');

            if ($enabled && is_string($key) && $key !== '') {
                return new OpenAiClinicalSummarizer;
            }

            return new NullClinicalSummarizer;
        });

        $this->app->singleton(MedicalDocumentEntityExtractor::class, function (): MedicalDocumentEntityExtractor {
            $openAi = new OpenAiMedicalDocumentEntityExtractor;

            return $openAi->isAvailable()
                ? $openAi
                : new NullMedicalDocumentEntityExtractor;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        MedicalRecord::observe(MedicalRecordObserver::class);
        PatientDisease::observe(PatientDiseaseObserver::class);
        PatientMedication::observe(PatientMedicationObserver::class);

        Filament::serving(function (): void {
            app()->setLocale('ar');
        });

        Event::listen(Login::class, RecordUserFirstLogin::class);
    }
}
