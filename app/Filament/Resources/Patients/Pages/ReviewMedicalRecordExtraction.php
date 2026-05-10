<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use App\Models\MedicalRecord;
use App\Services\ApplyMedicalRecordExtractedEntities;
use App\Support\MedicalRecordExtractionDraft;
use App\Support\MedicalRecordExtractionReviewSchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Livewire\Attributes\Locked;

/**
 * @property-read Schema $form
 */
class ReviewMedicalRecordExtraction extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PatientResource::class;

    protected static ?string $title = 'مراجعة اعتماد المستخرج';

    protected static ?string $breadcrumb = 'اعتماد المستخرج';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    #[Locked]
    public int $medicalRecordId = 0;

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function mount(int|string $record, int|string $medicalRecord): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
        abort_unless(auth()->user()?->canViewSensitiveClinicalData(), 403);

        $attachment = MedicalRecord::query()
            ->where('patient_id', $this->getRecord()->getKey())
            ->findOrFail((int) $medicalRecord);

        abort_unless(MedicalRecordExtractionReviewSchema::canOpenReviewWorkspace($attachment), 404);

        $this->medicalRecordId = $attachment->getKey();

        $this->form->fill(MedicalRecordExtractionDraft::initialStateForRecord($attachment));
    }

    protected function getMedicalRecord(): MedicalRecord
    {
        return MedicalRecord::query()
            ->where('patient_id', $this->getRecord()->getKey())
            ->findOrFail($this->medicalRecordId);
    }

    protected function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(
            MedicalRecordExtractionReviewSchema::buildFormComponents($this->getMedicalRecord())
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                EmbeddedSchema::make('form'),
            ])
                ->id('review-extraction-form')
                ->livewireSubmitHandler('submitReview')
                ->footer([
                    Actions::make($this->getReviewFooterActions()),
                ]),
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getReviewFooterActions(): array
    {
        return [
            Action::make('saveDraft')
                ->label('حفظ مسودة')
                ->color('gray')
                ->action(function (): void {
                    $this->saveDraft();
                })
                ->visible(fn (): bool => auth()->user()?->can('manageMedicalRecordExtraction', $this->getMedicalRecord())
                    && $this->getMedicalRecord()->extracted_entities_applied_at === null
                    && MedicalRecordExtractionReviewSchema::hasReviewableEntities($this->getMedicalRecord())),
            Action::make('submitReview')
                ->label('اعتماد المختار')
                ->submit('submitReview')
                ->visible(fn (): bool => auth()->user()?->can('manageMedicalRecordExtraction', $this->getMedicalRecord())
                    && $this->getMedicalRecord()->extracted_entities_applied_at === null
                    && MedicalRecordExtractionReviewSchema::hasReviewableEntities($this->getMedicalRecord())),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToPatient')
                ->label('العودة لتعديل المريض')
                ->url(fn (): string => $this->getResourceUrl('edit', ['record' => $this->getRecord()]))
                ->color('gray'),
        ];
    }

    public function saveDraft(): void
    {
        $attachment = $this->getMedicalRecord();

        abort_unless(auth()->user()?->can('manageMedicalRecordExtraction', $attachment), 403);

        if ($attachment->extracted_entities_applied_at !== null) {
            Notification::make()
                ->title('لا يمكن حفظ مسودة بعد الاعتماد')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $result = MedicalRecordExtractionDraft::persistForRecord($attachment, $data);

        if ($result['sanitized'] === null && ! $result['had_stored_draft']) {
            Notification::make()
                ->title('لا يوجد محتوى للمسودة')
                ->body('اختر بنوداً أو أدخل تصحيحات أسماء ثم احفظ مجدداً.')
                ->warning()
                ->send();

            return;
        }

        if ($result['sanitized'] === null) {
            Notification::make()
                ->title('تم مسح المسودة')
                ->body('أُزيلت المسودة المحفوظة لأن النموذج أصبح فارغاً.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('تم حفظ المسودة')
            ->body('يمكنك متابعة المراجعة لاحقاً من نفس الصفحة أو من «اعتماد سريع».')
            ->success()
            ->send();
    }

    public function submitReview(): void
    {
        $attachment = $this->getMedicalRecord();

        abort_unless(auth()->user()?->can('manageMedicalRecordExtraction', $attachment), 403);

        if ($attachment->extracted_entities_applied_at !== null) {
            Notification::make()
                ->title('تم الاعتماد مسبقاً')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();

        if (MedicalRecordExtractionReviewSchema::selectionIsEmpty($data)) {
            Notification::make()
                ->title('لم يُاختر أي بند')
                ->body('اختر عنصراً واحداً على الأقل.')
                ->danger()
                ->send();

            return;
        }

        $stats = app(ApplyMedicalRecordExtractedEntities::class)->apply($attachment, [
            'conditions' => $data['condition_indices'] ?? [],
            'allergies' => $data['allergy_indices'] ?? [],
            'medications' => $data['medication_indices'] ?? [],
            'lab_indices' => $data['lab_indices'] ?? [],
            'condition_name_fixes' => $data['condition_name_fixes'] ?? [],
            'allergy_name_fixes' => $data['allergy_name_fixes'] ?? [],
            'medication_name_fixes' => $data['medication_name_fixes'] ?? [],
        ], auth()->id());

        Notification::make()
            ->title('تم اعتماد الاستخراج')
            ->body(sprintf(
                'أمراض/حساسية: %d — أدوية: %d — تخطّي: %d — مختبر (خط زمني): %d',
                $stats['diseases_created'],
                $stats['medications_created'],
                $stats['skipped_duplicates'],
                $stats['lab_timeline_events'],
            ))
            ->success()
            ->send();

        $this->redirect($this->getResourceUrl('edit', ['record' => $this->getRecord()]));
    }
}
