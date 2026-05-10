<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if ($user?->role === UserRole::Patient) {
            $patient = $user->patients()->first();
            if ($patient !== null) {
                $this->redirect(PatientResource::getUrl('view', ['record' => $patient]));
            }
        }
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()?->role === UserRole::Patient) {
            return [];
        }

        return [
            Action::make('patientSelfRegister')
                ->label('تسجيل مريض (بوابة المريض)')
                ->url(route('patient.register'))
                ->icon(Heroicon::OutlinedUserPlus)
                ->openUrlInNewTab(false),
        ];
    }
}
