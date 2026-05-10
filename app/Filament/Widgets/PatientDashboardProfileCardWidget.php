<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Widgets\Widget;

class PatientDashboardProfileCardWidget extends Widget
{
    protected static ?int $sort = -50;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.patient-dashboard-profile-card';

    public static function canView(): bool
    {
        return auth()->user()?->role === UserRole::Patient;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $patient = $user?->patients()->first();

        $profileViewUrl = $patient !== null
            ? PatientResource::getUrl('view', ['record' => $patient])
            : null;

        $profileEditUrl = $patient !== null
            ? PatientResource::getUrl('edit', ['record' => $patient])
            : null;

        return [
            'user' => $user,
            'patient' => $patient,
            'dashboardUrl' => url('/app'),
            'profileViewUrl' => $profileViewUrl,
            'profileEditUrl' => $profileEditUrl,
            'shareUrl' => route('patient.share'),
        ];
    }
}
