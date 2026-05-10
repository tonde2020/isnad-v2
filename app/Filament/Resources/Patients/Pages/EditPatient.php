<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Contracts\ClinicalSummarizer;
use App\Enums\UserRole;
use App\Filament\Resources\Patients\PatientResource;
use App\Jobs\GeneratePatientClinicalSummaryJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPatient extends EditRecord
{
    protected static string $resource = PatientResource::class;

    /**
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
            ViewAction::make()
                ->label('عرض فقط'),
            Action::make('generateClinicalAiSummary')
                ->label('توليد ملخص AI مساعد')
                ->icon(Heroicon::OutlinedSparkles)
                ->requiresConfirmation()
                ->modalHeading('توليد ملخص مساعد للطبيب')
                ->modalDescription('يُرسل إلى مزود الذكاء الاصطناعي سياقاً سريرياً بدون الاسم أو الهاتف أو الرقم الوطني. راجع الضوابط القانونية وموافقة المريض عند الحاجة.')
                ->visible(fn (): bool => auth()->user()?->canViewSensitiveClinicalData() ?? false)
                ->disabled(fn (): bool => $this->record->ai_summary_disabled || ! app(ClinicalSummarizer::class)->isAvailable())
                ->action(function (): void {
                    GeneratePatientClinicalSummaryJob::dispatch($this->record->getKey(), auth()->id())->afterCommit();

                    Notification::make()
                        ->title('تم إرسال طلب التلخيص')
                        ->body('سيُحدَّث الحقل بعد انتهاء المعالجة في الخلفية.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->role !== UserRole::Patient),
        ];
    }
}
