<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\PatientDiseaseKind;
use App\Enums\UserRole;
use App\Models\DiseaseMaster;
use App\Models\MedicationMaster;
use App\Support\MedicalPrivateStorageUrl;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class PatientForm
{
    /**
     * واجهة المشرف التشغيلي: لا تُعرض أي حقول طبية أو تعريفية — رقم السجل فقط.
     */
    public static function configureOperationalAdminShell(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('operational_privacy_notice')
                    ->columnSpanFull()
                    ->content(new HtmlString(
                        '<p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">'.
                        'لا يطلع حساب المشرف التشغيلي على اسم المريض أو بياناته الطبية. يُعرض <strong>رقم السجل</strong> و<strong>تاريخ الانضمام</strong> و<strong>أول تسجيل دخول للحساب</strong> فقط؛ التفاصيل الكاملة متاحة للمريض صاحب الحساب.</p>'
                    )),
                TextInput::make('registry_number')
                    ->label('رقم السجل')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('created_at')
                    ->label('تاريخ الانضمام')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state): string {
                        if ($state === null || $state === '') {
                            return '—';
                        }

                        return Carbon::parse($state)
                            ->timezone(config('app.timezone'))
                            ->format('Y-m-d H:i');
                    }),
                TextInput::make('account_first_login_at')
                    ->label('أول تسجيل دخول للحساب')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state): string {
                        if ($state === null || $state === '') {
                            return 'لم يُسجَّل بعد';
                        }

                        return Carbon::parse($state)
                            ->timezone(config('app.timezone'))
                            ->format('Y-m-d H:i');
                    }),
            ]);
    }

    public static function configure(Schema $schema): Schema
    {
        $clinicalStaff = fn (): bool => auth()->user()?->canViewSensitiveClinicalData() ?? false;
        $patientAccount = fn (): bool => auth()->user()?->role === UserRole::Patient;
        $fullMedicalTabs = fn (): bool => $clinicalStaff() || $patientAccount();

        $stampDiseaseRowForPatient = function (array $data): array {
            if (auth()->user()?->role === UserRole::Patient) {
                $data['source'] = 'patient';
                $data['is_confirmed'] = false;
            }

            return $data;
        };

        $stampMedicationRowForPatient = function (array $data): array {
            if (auth()->user()?->role === UserRole::Patient) {
                $data['source'] = 'patient';
                $data['is_confirmed'] = false;
            }

            return $data;
        };

        $persistStaffOnlyPivotFieldsForPatient = function (array $data, Model $record): array {
            if (auth()->user()?->role !== UserRole::Patient) {
                return $data;
            }

            if (! array_key_exists('source', $data)) {
                $data['source'] = $record->getAttribute('source');
            }

            if (! array_key_exists('is_confirmed', $data)) {
                $data['is_confirmed'] = $record->getAttribute('is_confirmed');
            }

            return $data;
        };

        $stripMedicalRecordStaffFields = function (array $data): array {
            if (auth()->user()?->role !== UserRole::Patient) {
                return $data;
            }

            foreach ([
                'extracted_entities',
                'extracted_entities_applied_at',
                'extracted_entities_applied_by',
                'extraction_review_draft',
                'ai_summary',
                'enhanced_file_path',
                'processed_at',
                'reviewed_by',
                'reviewed_at',
            ] as $key) {
                unset($data[$key]);
            }

            return $data;
        };

        return $schema
            ->components([
                Tabs::make('patient_tabs')
                    ->tabs([
                        Tab::make('البيانات الأساسية')
                            ->schema([
                                TextInput::make('registry_number')
                                    ->label('رقم السجل')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('يُنشأ تلقائياً بعد حفظ الملف لأول مرة')
                                    ->helperText('رقم فريد ثابت يعرّف ملفك في النظام.')
                                    ->columnSpanFull(),
                                TextInput::make('full_name')
                                    ->label('الاسم الكامل')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('national_id')
                                    ->label('الرقم الوطني')
                                    ->maxLength(255),
                                DatePicker::make('birth_date')
                                    ->label('تاريخ الميلاد')
                                    ->native(false),
                                TextInput::make('blood_type')
                                    ->label('فصيلة الدم')
                                    ->maxLength(20)
                                    ->placeholder('مثال: O+'),
                                Select::make('gender')
                                    ->label('الجنس')
                                    ->options([
                                        'male' => 'ذكر',
                                        'female' => 'أنثى',
                                        'other' => 'آخر',
                                        'unknown' => 'غير محدد',
                                    ])
                                    ->native(false),
                                TextInput::make('state')
                                    ->label('الولاية')
                                    ->maxLength(120),
                                TextInput::make('locality')
                                    ->label('المحلية')
                                    ->maxLength(120),
                                TextInput::make('displacement_area')
                                    ->label('منطقة النزوح / الإقامة')
                                    ->maxLength(255),
                                TextInput::make('emergency_contact_name')
                                    ->label('جهة اتصال للطوارئ — الاسم')
                                    ->maxLength(255),
                                TextInput::make('emergency_contact_phone')
                                    ->label('جهة اتصال للطوارئ — الهاتف')
                                    ->tel()
                                    ->maxLength(64),
                                Select::make('user_id')
                                    ->label('ربط بحساب مستخدم (اختياري)')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->visible(fn (): bool => false)
                                    ->dehydrated(false),
                                Placeholder::make('patient_basic_notice')
                                    ->label('ملفك الطبي')
                                    ->content('أنت تدير بياناتك بنفسك. الطبيب لا يطلع على تفاصيل الملف إلا إذا أنشأت رابطاً مؤقتاً من «مشاركة مع الطبيب» وشاركته بنفسك.')
                                    ->columnSpanFull()
                                    ->visible($patientAccount),
                                Placeholder::make('clinical_gate_notice')
                                    ->label('سرية سريرية')
                                    ->content('البيانات الطبية التفصيلية لا تُعرض لحساب المشرف التشغيلي. يطلع عليها المريض من حسابه والطبيب عند مشاركة رابط موقّع فقط.')
                                    ->columnSpanFull()
                                    ->visible(fn (): bool => auth()->user()?->role === UserRole::Admin && ! $clinicalStaff()),
                                Toggle::make('ai_summary_disabled')
                                    ->label('إيقاف ملخص الذكاء الاصطناعي لهذا المريض')
                                    ->helperText('لن يُعرض ملخص AI للطبيب ولن يُنصح بتوليد ملخص عبر لوحة التحكم.')
                                    ->visible($clinicalStaff)
                                    ->default(false),
                                Textarea::make('clinical_ai_summary')
                                    ->label('ملخص مساعد (AI) — للقراءة فقط')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->rows(8)
                                    ->columnSpanFull()
                                    ->helperText('يُحدَّث من الخلفية بعد تشغيل الإجراء «توليد ملخص AI». هذا ليس تشخيصاً ولا توصية علاجية.')
                                    ->visible($clinicalStaff),
                            ])
                            ->columns(2),
                        Tab::make('الأمراض والحساسية')
                            ->schema([
                                Repeater::make('patientChronicDiseases')
                                    ->relationship()
                                    ->label('الأمراض المزمنة (قائمة + إضافة يدوية)')
                                    ->schema([
                                        Hidden::make('kind')
                                            ->default(PatientDiseaseKind::Chronic->value),
                                        Select::make('disease_master_id')
                                            ->label('اختر من القائمة')
                                            ->searchable()
                                            ->getSearchResultsUsing(function (?string $search): array {
                                                $query = DiseaseMaster::query()
                                                    ->where('is_active', true)
                                                    ->where('category', '!=', 'allergy');

                                                if (filled($search)) {
                                                    $query->where(function ($q) use ($search) {
                                                        $q->where('name_ar', 'like', '%'.$search.'%')
                                                            ->orWhere('name_en', 'like', '%'.$search.'%');
                                                    });
                                                }

                                                return $query->orderBy('name_ar')
                                                    ->limit(40)
                                                    ->get()
                                                    ->mapWithKeys(fn (DiseaseMaster $disease) => [$disease->getKey() => $disease->name_ar])
                                                    ->all();
                                            })
                                            ->getOptionLabelUsing(fn ($value): ?string => DiseaseMaster::query()->find($value)?->name_ar),
                                        TextInput::make('custom_name')
                                            ->label('مرض غير موجود في القائمة')
                                            ->maxLength(255),
                                        Select::make('status')
                                            ->label('الحالة')
                                            ->options([
                                                'active' => 'نشط',
                                                'past' => 'سابق',
                                            ])
                                            ->default('active')
                                            ->native(false),
                                        DatePicker::make('diagnosed_at')
                                            ->label('تاريخ التشخيص')
                                            ->native(false),
                                        Select::make('severity')
                                            ->label('الشدة')
                                            ->options([
                                                'mild' => 'خفيف',
                                                'moderate' => 'متوسط',
                                                'severe' => 'شديد',
                                            ])
                                            ->native(false),
                                        Select::make('source')
                                            ->label('مصدر التسجيل')
                                            ->options([
                                                'admin' => 'إدارة النظام',
                                                'patient' => 'المريض',
                                                'import' => 'استيراد',
                                                'system' => 'نظام',
                                            ])
                                            ->default(fn (): string => $patientAccount() ? 'patient' : 'admin')
                                            ->disabled($patientAccount)
                                            ->dehydrated(true)
                                            ->native(false)
                                            ->visible($clinicalStaff),
                                        Toggle::make('is_confirmed')
                                            ->label('مؤكد طبياً')
                                            ->default(false)
                                            ->visible($clinicalStaff),
                                        Textarea::make('notes')
                                            ->label('ملاحظات')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->mutateRelationshipDataBeforeCreateUsing($stampDiseaseRowForPatient)
                                    ->mutateRelationshipDataBeforeSaveUsing($persistStaffOnlyPivotFieldsForPatient),
                                Repeater::make('patientAllergyRecords')
                                    ->relationship()
                                    ->label('الحساسية')
                                    ->schema([
                                        Hidden::make('kind')
                                            ->default(PatientDiseaseKind::Allergy->value),
                                        Select::make('disease_master_id')
                                            ->label('نوع الحساسية (من القائمة)')
                                            ->searchable()
                                            ->getSearchResultsUsing(function (?string $search): array {
                                                $query = DiseaseMaster::query()
                                                    ->where('is_active', true)
                                                    ->where('category', 'allergy');

                                                if (filled($search)) {
                                                    $query->where(function ($q) use ($search) {
                                                        $q->where('name_ar', 'like', '%'.$search.'%')
                                                            ->orWhere('name_en', 'like', '%'.$search.'%');
                                                    });
                                                }

                                                return $query->orderBy('name_ar')
                                                    ->limit(40)
                                                    ->get()
                                                    ->mapWithKeys(fn (DiseaseMaster $disease) => [$disease->getKey() => $disease->name_ar])
                                                    ->all();
                                            })
                                            ->getOptionLabelUsing(fn ($value): ?string => DiseaseMaster::query()->find($value)?->name_ar),
                                        TextInput::make('custom_name')
                                            ->label('تفاصيل حساسية إضافية')
                                            ->maxLength(255),
                                        Select::make('status')
                                            ->label('الحالة')
                                            ->options([
                                                'active' => 'نشط',
                                                'past' => 'سابق',
                                            ])
                                            ->default('active')
                                            ->native(false),
                                        DatePicker::make('diagnosed_at')
                                            ->label('تاريخ التسجيل')
                                            ->native(false),
                                        Select::make('severity')
                                            ->label('الشدة')
                                            ->options([
                                                'mild' => 'خفيف',
                                                'moderate' => 'متوسط',
                                                'severe' => 'شديد',
                                            ])
                                            ->native(false),
                                        Select::make('source')
                                            ->label('مصدر التسجيل')
                                            ->options([
                                                'admin' => 'إدارة النظام',
                                                'patient' => 'المريض',
                                                'import' => 'استيراد',
                                                'system' => 'نظام',
                                            ])
                                            ->default(fn (): string => $patientAccount() ? 'patient' : 'admin')
                                            ->disabled($patientAccount)
                                            ->dehydrated(true)
                                            ->native(false)
                                            ->visible($clinicalStaff),
                                        Toggle::make('is_confirmed')
                                            ->label('مؤكد')
                                            ->default(false)
                                            ->visible($clinicalStaff),
                                        Textarea::make('notes')
                                            ->label('ملاحظات')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->mutateRelationshipDataBeforeCreateUsing($stampDiseaseRowForPatient)
                                    ->mutateRelationshipDataBeforeSaveUsing($persistStaffOnlyPivotFieldsForPatient),
                                Textarea::make('chronic_diseases')
                                    ->label('ملاحظات نصية إضافية (مزمنة)')
                                    ->helperText('اختياري — للتفاصيل الحرة بالإضافة للقائمة أعلاه.')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible($fullMedicalTabs),
                                Textarea::make('allergies')
                                    ->label('ملاحظات نصية إضافية (حساسية)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible($fullMedicalTabs),
                            ])
                            ->visible($fullMedicalTabs),
                        Tab::make('الأدوية الحالية')
                            ->schema([
                                Repeater::make('patientMedications')
                                    ->relationship()
                                    ->label('الأدوية')
                                    ->schema([
                                        Select::make('medication_master_id')
                                            ->label('الدواء (اكتب 3 أحرف على الأقل للبحث)')
                                            ->searchable()
                                            ->getSearchResultsUsing(function (?string $search): array {
                                                if ($search === null || mb_strlen($search) < 3) {
                                                    return [];
                                                }

                                                return MedicationMaster::query()
                                                    ->where('is_active', true)
                                                    ->where(function ($q) use ($search) {
                                                        $q->where('brand_name', 'like', '%'.$search.'%')
                                                            ->orWhere('generic_name', 'like', '%'.$search.'%');
                                                    })
                                                    ->orderBy('generic_name')
                                                    ->limit(20)
                                                    ->get()
                                                    ->mapWithKeys(fn (MedicationMaster $medication) => [
                                                        $medication->getKey() => $medication->displayLabel(),
                                                    ])
                                                    ->all();
                                            })
                                            ->getOptionLabelUsing(function ($value): ?string {
                                                $medication = MedicationMaster::query()->find($value);

                                                return $medication?->displayLabel();
                                            }),
                                        TextInput::make('custom_medication_name')
                                            ->label('دواء غير موجود في القائمة')
                                            ->maxLength(255)
                                            ->visible(fn ($get) => blank($get('medication_master_id'))),
                                        TextInput::make('dosage')
                                            ->label('الجرعة')
                                            ->maxLength(255),
                                        TextInput::make('frequency')
                                            ->label('التكرار')
                                            ->maxLength(255),
                                        TextInput::make('duration')
                                            ->label('المدة')
                                            ->maxLength(255),
                                        Textarea::make('instructions')
                                            ->label('تعليمات')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        DatePicker::make('start_date')
                                            ->label('تاريخ البدء')
                                            ->native(false),
                                        DatePicker::make('stopped_at')
                                            ->label('تاريخ الإيقاف')
                                            ->native(false),
                                        Textarea::make('stop_reason')
                                            ->label('سبب الإيقاف')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Select::make('source')
                                            ->label('مصدر التسجيل')
                                            ->options([
                                                'admin' => 'إدارة النظام',
                                                'patient' => 'المريض',
                                                'import' => 'استيراد',
                                                'system' => 'نظام',
                                            ])
                                            ->default(fn (): string => $patientAccount() ? 'patient' : 'admin')
                                            ->disabled($patientAccount)
                                            ->dehydrated(true)
                                            ->native(false)
                                            ->visible($clinicalStaff),
                                        Toggle::make('is_confirmed')
                                            ->label('مؤكد طبياً')
                                            ->default(false)
                                            ->visible($clinicalStaff),
                                        Toggle::make('is_active')
                                            ->label('نشط')
                                            ->default(true),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->mutateRelationshipDataBeforeCreateUsing($stampMedicationRowForPatient)
                                    ->mutateRelationshipDataBeforeSaveUsing($persistStaffOnlyPivotFieldsForPatient),
                            ])
                            ->visible($fullMedicalTabs),
                        Tab::make('المرفقات والمستندات')
                            ->schema([
                                Repeater::make('medicalRecords')
                                    ->relationship()
                                    ->label('المرفقات')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('عنوان المرفق')
                                            ->maxLength(255),
                                        Select::make('record_type')
                                            ->label('نوع المستند')
                                            ->options([
                                                'prescription' => 'وصفة',
                                                'lab' => 'تحاليل',
                                                'imaging' => 'أشعة',
                                                'report' => 'تقرير',
                                                'other' => 'أخرى',
                                            ])
                                            ->native(false),
                                        FileUpload::make('file_path')
                                            ->label('الملف (تخزين خاص)')
                                            ->disk('medical_private')
                                            ->directory('medical-records')
                                            ->visibility('private')
                                            ->required()
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
                                            ->helperText('من الجوال: زر الرفع يعرض غالباً «التقاط صورة» أو «الكاميرا» بفضل خاصية الكاميرا؛ استخدمها لمسح المستند، أو اختر ملفاً أو PDF من المعرض. بعد الحفظ يُشغَّل استخراج النص على الخادم للصور إذا وُجد Tesseract؛ يمكنك لاحقاً تعديل نص OCR يدوياً أو اعتماد المستخرج من ملفك.')
                                            ->columnSpanFull()
                                            ->getUploadedFileUsing(fn (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array => MedicalPrivateStorageUrl::filamentUploadedFilePayload($component, $file, $storedFileNames))
                                            ->getOpenableFileUrlUsing(fn (string $file): string => MedicalPrivateStorageUrl::signedStreamUrl($file))
                                            ->getDownloadableFileUrlUsing(fn (string $file): string => MedicalPrivateStorageUrl::signedStreamUrl($file)),
                                        Select::make('processing_status')
                                            ->label('حالة المعالجة')
                                            ->options([
                                                'pending' => 'قيد الانتظار',
                                                'processing' => 'جاري المعالجة',
                                                'completed' => 'مكتمل',
                                                'failed' => 'فشل',
                                            ])
                                            ->default('pending')
                                            ->native(false)
                                            ->visible($clinicalStaff),
                                        TextInput::make('file_type')
                                            ->label('نوع الملف')
                                            ->maxLength(50)
                                            ->placeholder('pdf أو صورة')
                                            ->visible($clinicalStaff),
                                        Textarea::make('description')
                                            ->label('وصف')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        DatePicker::make('record_date')
                                            ->label('تاريخ المستند')
                                            ->native(false),
                                        Toggle::make('is_reviewed')
                                            ->label('تمت مراجعة المرفق')
                                            ->default(false)
                                            ->visible($clinicalStaff),
                                        Textarea::make('ocr_text')
                                            ->label('نص مستخرج (OCR)')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->visible($clinicalStaff),
                                        Textarea::make('extracted_entities')
                                            ->label('بيانات مستخرجة مقترحة (JSON — للمراجعة فقط)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->rows(8)
                                            ->columnSpanFull()
                                            ->formatStateUsing(fn ($state) => is_array($state)
                                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                                : (string) $state)
                                            ->helperText('لا تُضاف تلقائياً لملف المريض. راجعها وادخل الأدوية/الأمراض يدوياً بعد التأكد.')
                                            ->visible($clinicalStaff),
                                        Textarea::make('ai_summary')
                                            ->label('ملخص مساعد (AI) للمرفق')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->helperText('للمساعدة على القراءة فقط — وليس تشخيصاً.')
                                            ->visible($clinicalStaff),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data) use ($stripMedicalRecordStaffFields): array {
                                        $data = $stripMedicalRecordStaffFields($data);
                                        if (auth()->user()?->role === UserRole::Patient) {
                                            $data['processing_status'] = 'pending';
                                            $data['is_reviewed'] = false;
                                        }

                                        return $data;
                                    })
                                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Model $record) use ($stripMedicalRecordStaffFields): array {
                                        $data = $stripMedicalRecordStaffFields($data);
                                        if (auth()->user()?->role === UserRole::Patient) {
                                            unset($data['processing_status'], $data['is_reviewed']);
                                        }

                                        return $data;
                                    }),
                            ])
                            ->visible($fullMedicalTabs),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
