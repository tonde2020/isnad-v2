<?php

namespace App\Support;

use App\Models\MedicalRecord;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

final class MedicalRecordExtractionReviewSchema
{
    /**
     * @return array<int, Component>
     */
    public static function buildFormComponents(MedicalRecord $record): array
    {
        $entities = is_array($record->extracted_entities) ? $record->extracted_entities : [];

        $components = [];

        $ocrText = filled($record->ocr_text)
            ? Str::limit((string) $record->ocr_text, 8000)
            : null;

        $components[] = Placeholder::make('ocr_preview')
            ->label('معاينة نص OCR')
            ->content(new HtmlString(
                $ocrText !== null
                    ? '<div class="fi-prose max-h-96 overflow-y-auto whitespace-pre-wrap text-sm text-gray-950 dark:text-gray-50">'.e($ocrText).'</div>'
                    : '<span class="text-gray-500 dark:text-gray-400">لا يوجد نص OCR بعد.</span>'
            ))
            ->columnSpanFull();

        $rawNotes = isset($entities['notes']) ? trim((string) $entities['notes']) : '';
        if ($rawNotes !== '') {
            $components[] = Placeholder::make('ai_notes')
                ->label('ملاحظات الاستخراج')
                ->content(new HtmlString('<div class="text-sm text-amber-800 dark:text-amber-200 whitespace-pre-wrap">'.e(Str::limit($rawNotes, 2000)).'</div>'))
                ->columnSpanFull();
        }

        if ($record->extracted_entities_applied_at !== null) {
            $components[] = Placeholder::make('already_applied')
                ->label('حالة الاعتماد')
                ->content(new HtmlString(
                    '<div class="text-sm text-success-700 dark:text-success-400">تم اعتماد هذا المستخرج في '
                    .e($record->extracted_entities_applied_at?->format('Y-m-d H:i') ?? '—')
                    .'.</div>'
                ))
                ->columnSpanFull();
        }

        $conditionOptions = self::indexOptions($entities['conditions'] ?? [], 'name');
        if ($conditionOptions !== []) {
            $components[] = CheckboxList::make('condition_indices')
                ->label('أمراض مزمنة مقترحة')
                ->options($conditionOptions)
                ->columns(1)
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        $allergyOptions = self::indexOptions($entities['allergies'] ?? [], 'name');
        if ($allergyOptions !== []) {
            $components[] = CheckboxList::make('allergy_indices')
                ->label('حساسية مقترحة')
                ->options($allergyOptions)
                ->columns(1)
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        $medicationOptions = self::indexMedicationOptions($entities['medications'] ?? []);
        if ($medicationOptions !== []) {
            $components[] = CheckboxList::make('medication_indices')
                ->label('أدوية مقترحة')
                ->options($medicationOptions)
                ->columns(1)
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        $labOptions = self::indexLabOptions($entities['lab_results'] ?? []);
        if ($labOptions !== []) {
            $components[] = CheckboxList::make('lab_indices')
                ->label('نتائج مختبر مقترحة (خط زمني فقط)')
                ->helperText('لا تُضاف كتشخيص؛ تُسجَّل كحدث توثيقي.')
                ->options($labOptions)
                ->columns(1)
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        if ($conditionOptions !== []) {
            $components[] = KeyValue::make('condition_name_fixes')
                ->label('تصحيح أسماء الأمراض المزمنة (اختياري)')
                ->helperText('المفتاح = فهرس البند (0، 1، …). القيمة = الاسم المعتمد.')
                ->keyLabel('الفهرس')
                ->valueLabel('الاسم المعتمد')
                ->columnSpanFull()
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        if ($allergyOptions !== []) {
            $components[] = KeyValue::make('allergy_name_fixes')
                ->label('تصحيح أسماء الحساسية (اختياري)')
                ->keyLabel('الفهرس')
                ->valueLabel('الاسم المعتمد')
                ->columnSpanFull()
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        if ($medicationOptions !== []) {
            $components[] = KeyValue::make('medication_name_fixes')
                ->label('تصحيح أسماء الأدوية (اختياري)')
                ->keyLabel('الفهرس')
                ->valueLabel('الاسم المعتمد')
                ->columnSpanFull()
                ->disabled(fn (): bool => $record->extracted_entities_applied_at !== null);
        }

        $hasSelectable = $conditionOptions !== []
            || $allergyOptions !== []
            || $medicationOptions !== []
            || $labOptions !== [];

        if (! $hasSelectable && $ocrText === null && $rawNotes === '') {
            $components[] = Placeholder::make('empty_payload')
                ->label('')
                ->content('لا توجد بيانات مستخرجة لهذا المرفق.');
        }

        return $components;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function selectionIsEmpty(array $data): bool
    {
        return ($data['condition_indices'] ?? []) === []
            && ($data['allergy_indices'] ?? []) === []
            && ($data['medication_indices'] ?? []) === []
            && ($data['lab_indices'] ?? []) === [];
    }

    public static function hasExtractedPayload(MedicalRecord $record): bool
    {
        $entities = $record->extracted_entities;

        return is_array($entities) && $entities !== [];
    }

    public static function canOpenReviewWorkspace(MedicalRecord $record): bool
    {
        return self::hasExtractedPayload($record) || filled($record->ocr_text);
    }

    public static function hasReviewableEntities(MedicalRecord $record): bool
    {
        if (! self::hasExtractedPayload($record)) {
            return false;
        }

        $entities = $record->extracted_entities;

        return self::indexOptions($entities['conditions'] ?? [], 'name') !== []
            || self::indexOptions($entities['allergies'] ?? [], 'name') !== []
            || self::indexMedicationOptions($entities['medications'] ?? []) !== []
            || self::indexLabOptions($entities['lab_results'] ?? []) !== [];
    }

    /**
     * @param  list<mixed>  $rows
     * @return array<int|string, string>
     */
    public static function indexOptions(array $rows, string $labelKey): array
    {
        $options = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row[$labelKey] ?? ''));
            if ($label === '') {
                continue;
            }

            $confidence = $row['confidence'] ?? null;
            $suffix = is_numeric($confidence) ? ' — ثقة: '.$confidence : '';

            $options[$index] = $label.$suffix;
        }

        return $options;
    }

    /**
     * @param  list<mixed>  $rows
     * @return array<int|string, string>
     */
    public static function indexMedicationOptions(array $rows): array
    {
        $options = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['name'] ?? ''));
            if ($label === '') {
                continue;
            }

            $confidence = $row['confidence'] ?? null;
            $suffix = is_numeric($confidence) ? ' — ثقة: '.$confidence : '';

            $dosage = isset($row['dosage']) ? trim((string) $row['dosage']) : '';
            if ($dosage !== '') {
                $suffix .= ' — جرعة: '.$dosage;
            }

            $options[$index] = $label.$suffix;
        }

        return $options;
    }

    /**
     * @param  list<mixed>  $rows
     * @return array<int|string, string>
     */
    public static function indexLabOptions(array $rows): array
    {
        $options = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['test_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $value = trim((string) ($row['value'] ?? ''));
            $unit = trim((string) ($row['unit'] ?? ''));
            $suffix = $value.($unit !== '' ? ' '.$unit : '');

            $confidence = $row['confidence'] ?? null;
            if (is_numeric($confidence)) {
                $suffix .= ' — ثقة: '.$confidence;
            }

            $options[$index] = $name.($suffix !== '' ? ': '.$suffix : '');
        }

        return $options;
    }
}
