<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Enums\UserRole;
use App\Filament\Resources\Patients\PatientResource;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\ApplyMedicalRecordExtractedEntities;
use App\Support\MedicalPrivateStorageUrl;
use App\Support\MedicalRecordExtractionDraft;
use App\Support\MedicalRecordExtractionReviewSchema;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class MedicalRecordsRelationManager extends RelationManager
{
    /**
     * @return array<string, string>
     */
    private static function recordTypeOptions(): array
    {
        return [
            'prescription' => 'وصفة',
            'lab' => 'تحاليل',
            'imaging' => 'أشعة',
            'report' => 'تقرير',
            'other' => 'أخرى',
        ];
    }

    /**
     * تعطيل التحميل الكسول الافتراضي لتفادي وميض placeholder ثم الجدول (يبدو كأن القسم يظهر ويختفي).
     */
    protected static bool $isLazy = false;

    protected static string $relationship = 'medicalRecords';

    protected static ?string $title = 'المرفقات واعتماد الاستخراج';

    protected static ?string $modelLabel = 'مرفق';

    protected static ?string $pluralModelLabel = 'مرفقات';

    /**
     * السماح بالرفع من صفحة العرض أيضاً؛ الواجهة الافتراضية تجعل مديري العلاقات قراءة فقط على صفحة العرض.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        if ($user?->role === UserRole::Patient) {
            return isset($ownerRecord->user_id)
                && (int) $ownerRecord->user_id === (int) $user->getKey()
                && parent::canViewForRecord($ownerRecord, $pageClass);
        }

        if (! $user?->canViewSensitiveClinicalData()) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('عنوان المرفق')
                ->maxLength(255),
            Select::make('record_type')
                ->label('نوع المستند')
                ->options(self::recordTypeOptions())
                ->native(false),
            FileUpload::make('file_path')
                ->label('الملف (تخزين خاص)')
                ->disk('medical_private')
                ->directory('medical-records')
                ->visibility('private')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ])
                ->maxSize(10240)
                ->downloadable()
                ->openable()
                ->extraInputAttributes([
                    'capture' => 'environment',
                    'accept' => 'application/pdf,image/jpeg,image/png,image/webp,image/*',
                ])
                ->helperText('من الجوال يمكن اختيار الكاميرا أو المعرض. الصيغ: PDF أو صورة.')
                ->columnSpanFull()
                ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array => MedicalPrivateStorageUrl::filamentUploadedFilePayload($component, $file, $storedFileNames))
                ->getOpenableFileUrlUsing(fn (string $file): string => MedicalPrivateStorageUrl::signedStreamUrl($file))
                ->getDownloadableFileUrlUsing(fn (string $file): string => MedicalPrivateStorageUrl::signedStreamUrl($file)),
            Textarea::make('description')
                ->label('وصف')
                ->rows(2)
                ->columnSpanFull(),
            DatePicker::make('record_date')
                ->label('تاريخ المستند')
                ->native(false),
            Textarea::make('ocr_text')
                ->label('نص مستخرج (OCR)')
                ->rows(6)
                ->columnSpanFull()
                ->helperText('يُملأ تلقائياً على الخادم للصور (JPEG/PNG/WebP) عند ضبط Tesseract ولغات ara+eng. ملفات PDF لا تُعرَّض حالياً لـ OCR في هذا الإصدار — يمكنك لصق النص يدوياً هنا إن احتجت. الحالة «مكتمل» تعني انتهاء خط المعالجة، وليست بالضرورة وجود نص OCR.')
                ->hiddenOn('create'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(40)
                    ->tooltip(fn (MedicalRecord $record): ?string => $record->title),
                TextColumn::make('record_type')
                    ->label('النوع')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('processing_status')
                    ->label('المعالجة')
                    ->badge(),
                IconColumn::make('ocr_text')
                    ->label('نص OCR')
                    ->boolean(fn (MedicalRecord $record): bool => filled($record->ocr_text))
                    ->tooltip(fn (MedicalRecord $record): string => filled($record->ocr_text)
                        ? 'يوجد نص — افتح «تعديل» لقراءته أو تصحيحه.'
                        : 'لا يوجد نص بعد (PDF، أو Tesseract غير مضبوط، أو فشل القراءة).'),
                IconColumn::make('extracted_entities')
                    ->label('مستخرج')
                    ->boolean(fn (MedicalRecord $record): bool => MedicalRecordExtractionReviewSchema::hasExtractedPayload($record)),
                IconColumn::make('extraction_review_draft')
                    ->label('مسودة')
                    ->boolean(fn (MedicalRecord $record): bool => filled($record->extraction_review_draft))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('extracted_entities_applied_at')
                    ->label('اعتماد الاستخراج')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('لم يُعتمد')
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة مرفق')
                    ->icon(Heroicon::OutlinedPlus)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['processing_status'] = 'pending';
                        $data['is_reviewed'] = false;

                        return $data;
                    })
                    ->after(fn () => $this->resetTable()),
                Action::make('captureFromCamera')
                    ->label('التقط بالكاميرا')
                    ->icon(Heroicon::OutlinedCamera)
                    ->modalHeading('إضافة مرفق من الكاميرا')
                    ->modalDescription('مخصّص للهاتف: يطلب التقاط صورة بالكاميرا الخلفية عندما يدعم المتصفح ذلك. بعد الحفظ تُشغَّل المعالجة على الخادم مثل أي مرفق (استخراج نص للصور عند توفر Tesseract، ثم اقتراح كيانات عند تفعيل الذكاء الاصطناعي).')
                    ->modalSubmitActionLabel('حفظ وبدء التحليل')
                    ->modalWidth(Width::Large)
                    ->visible(fn (): bool => auth()->user()?->can('create', MedicalRecord::class) ?? false)
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان المرفق')
                            ->placeholder('مثال: وصفة، نتيجة تحليل')
                            ->maxLength(255),
                        Select::make('record_type')
                            ->label('نوع المستند')
                            ->options(self::recordTypeOptions())
                            ->default('other')
                            ->native(false),
                        FileUpload::make('camera_capture')
                            ->label('صورة من الكاميرا')
                            ->disk('medical_private')
                            ->directory('medical-records')
                            ->visibility('private')
                            ->required()
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(10240)
                            ->extraInputAttributes([
                                'capture' => 'environment',
                                'accept' => 'image/*',
                            ])
                            ->helperText('اضغط الحقل على الجوال واختر «الكاميرا» أو «التقاط صورة» إن ظهرت.')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('وصف')
                            ->rows(2)
                            ->columnSpanFull(),
                        DatePicker::make('record_date')
                            ->label('تاريخ المستند')
                            ->native(false),
                    ])
                    ->action(function (array $data): void {
                        Gate::authorize('create', MedicalRecord::class);

                        $owner = $this->getOwnerRecord();
                        abort_unless($owner instanceof Patient, 403);
                        abort_unless(
                            auth()->check()
                                && $owner->user_id !== null
                                && (int) $owner->user_id === (int) auth()->id(),
                            403,
                        );

                        $path = $data['camera_capture'] ?? null;
                        if (! filled($path)) {
                            Notification::make()
                                ->title('لم تُختَر صورة')
                                ->danger()
                                ->send();

                            return;
                        }

                        MedicalRecord::query()->create([
                            'patient_id' => $owner->getKey(),
                            'title' => filled($data['title'] ?? null) ? $data['title'] : null,
                            'record_type' => $data['record_type'] ?? 'other',
                            'file_path' => $path,
                            'description' => filled($data['description'] ?? null) ? $data['description'] : null,
                            'record_date' => $data['record_date'] ?? null,
                            'processing_status' => 'pending',
                            'is_reviewed' => false,
                        ]);

                        Notification::make()
                            ->title('تم حفظ الصورة')
                            ->body('جرى إضافة المرفق؛ ستُكمَل المعالجة والتحليل على الخادم تلقائياً.')
                            ->success()
                            ->send();
                    })
                    ->after(fn () => $this->resetTable()),
                Action::make('refreshMedicalRecordsStatuses')
                    ->label('تحديث الحالة')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->tooltip('إعادة قراءة حالة المعالجة والاستخراج من الخادم دون إعادة تحميل الصفحة.')
                    ->action(function (): void {
                        $this->resetTable();

                        Notification::make()
                            ->title('تم التحديث')
                            ->body('عُد تحميل قائمة المرفقات.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->after(fn () => $this->resetTable()),
                DeleteAction::make()
                    ->label('حذف')
                    ->after(fn () => $this->resetTable()),
                Action::make('openReviewPage')
                    ->label('مراجعة كاملة')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(function (MedicalRecord $record): string {
                        return PatientResource::getUrl('reviewMedicalRecordExtraction', [
                            'record' => $this->getOwnerRecord(),
                            'medicalRecord' => $record->getKey(),
                        ]);
                    })
                    ->visible(fn (MedicalRecord $record): bool => auth()->user()?->canViewSensitiveClinicalData()
                        && MedicalRecordExtractionReviewSchema::canOpenReviewWorkspace($record)),
                Action::make('applyExtractedEntities')
                    ->label('اعتماد سريع')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->modalHeading('اعتماد بيانات مستخرجة')
                    ->modalDescription('نفس الخيارات المتوفرة في صفحة «مراجعة كاملة». لإجراء أوضح مع معاينة OCR أوسع استخدم صفحة المراجعة.')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->visible(fn (MedicalRecord $record): bool => auth()->user()?->can('manageMedicalRecordExtraction', $record)
                        && MedicalRecordExtractionReviewSchema::hasReviewableEntities($record)
                        && $record->extracted_entities_applied_at === null)
                    ->disabled(fn (MedicalRecord $record): bool => ! auth()->user()?->can('manageMedicalRecordExtraction', $record))
                    ->fillForm(fn (MedicalRecord $record): array => MedicalRecordExtractionDraft::initialStateForRecord($record))
                    ->form(fn (MedicalRecord $record): array => MedicalRecordExtractionReviewSchema::buildFormComponents($record))
                    ->extraModalFooterActions([
                        Action::make('saveExtractionDraftQuick')
                            ->label('حفظ مسودة')
                            ->color('gray')
                            ->action(function (Action $action): void {
                                $parent = $action->getParentAction();
                                $record = $action->getRecord();

                                abort_unless($record instanceof MedicalRecord && auth()->user()?->can('manageMedicalRecordExtraction', $record), 403);

                                if ($record->extracted_entities_applied_at !== null) {
                                    Notification::make()
                                        ->title('لا يمكن حفظ مسودة')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $data = $parent?->getRawData() ?? [];
                                $result = MedicalRecordExtractionDraft::persistForRecord($record, $data);

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
                                    ->body('يمكنك متابعة التعديل ثم الاعتماد من هنا أو من صفحة المراجعة الكاملة.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->action(function (MedicalRecord $record, array $data): void {
                        if (MedicalRecordExtractionReviewSchema::selectionIsEmpty($data)) {
                            Notification::make()
                                ->title('لم يُاختر أي بند')
                                ->body('اختر عنصراً واحداً على الأقل للاعتماد.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $stats = app(ApplyMedicalRecordExtractedEntities::class)->apply($record, [
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
                    })
                    ->after(fn () => $this->resetTable()),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد مرفقات في هذا الملف')
            ->emptyStateDescription('اضغط «إضافة مرفق» لرفع PDF أو صورة، أو «التقط بالكاميرا» لالتقاط صورة مباشرة من الهاتف.');
    }
}
