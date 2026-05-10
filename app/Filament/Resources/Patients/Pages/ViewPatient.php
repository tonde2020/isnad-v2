<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    /**
     * منع تسريب أي حقل إلى حالة Livewire لحساب المشرف التشغيلي.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (auth()->user()?->role === UserRole::Admin) {
            $this->record->loadMissing('user');

            return [
                'registry_number' => $data['registry_number'] ?? null,
                'created_at' => $data['created_at'] ?? null,
                'account_first_login_at' => $this->record->user?->first_login_at,
            ];
        }

        return parent::mutateFormDataBeforeFill($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('تعديل')
                ->visible(fn (): bool => auth()->user()?->can('update', $this->record) ?? false),
        ];
    }
}
