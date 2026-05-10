<?php

namespace App\Support;

use App\Models\MedicalRecord;

/**
 * مسودة حقول نموذج مراجعة الاستخراج (بدون اعتماد في ملف المريض).
 */
final class MedicalRecordExtractionDraft
{
    /**
     * @return array<string, mixed>
     */
    public static function emptyState(): array
    {
        return [
            'condition_indices' => [],
            'allergy_indices' => [],
            'medication_indices' => [],
            'lab_indices' => [],
            'condition_name_fixes' => [],
            'allergy_name_fixes' => [],
            'medication_name_fixes' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function initialStateForRecord(MedicalRecord $record): array
    {
        $draft = $record->extraction_review_draft;

        if (! is_array($draft)) {
            return self::emptyState();
        }

        return array_merge(self::emptyState(), $draft);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null null إذا كانت المسودة فارغة تماماً
     */
    public static function sanitizeForStorage(array $data): ?array
    {
        $out = [
            'condition_indices' => array_values(array_map('intval', is_array($data['condition_indices'] ?? null) ? $data['condition_indices'] : [])),
            'allergy_indices' => array_values(array_map('intval', is_array($data['allergy_indices'] ?? null) ? $data['allergy_indices'] : [])),
            'medication_indices' => array_values(array_map('intval', is_array($data['medication_indices'] ?? null) ? $data['medication_indices'] : [])),
            'lab_indices' => array_values(array_map('intval', is_array($data['lab_indices'] ?? null) ? $data['lab_indices'] : [])),
            'condition_name_fixes' => self::normalizeFixesMap($data['condition_name_fixes'] ?? []),
            'allergy_name_fixes' => self::normalizeFixesMap($data['allergy_name_fixes'] ?? []),
            'medication_name_fixes' => self::normalizeFixesMap($data['medication_name_fixes'] ?? []),
        ];

        if (
            MedicalRecordExtractionReviewSchema::selectionIsEmpty($out)
            && self::fixesMapsAreEmpty($out['condition_name_fixes'])
            && self::fixesMapsAreEmpty($out['allergy_name_fixes'])
            && self::fixesMapsAreEmpty($out['medication_name_fixes'])
        ) {
            return null;
        }

        return $out;
    }

    /**
     * @param  array<mixed, mixed>  $fixes
     * @return array<string, string>
     */
    private static function normalizeFixesMap(mixed $fixes): array
    {
        if (! is_array($fixes)) {
            return [];
        }

        $out = [];
        foreach ($fixes as $k => $v) {
            $val = trim((string) $v);
            if ($val === '') {
                continue;
            }
            $out[(string) $k] = $val;
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $map
     */
    private static function fixesMapsAreEmpty(array $map): bool
    {
        return $map === [];
    }

    /**
     * يحفظ حالة النموذج بعد التطهير. يعيد ما تم تخزينه وما إذا كان السجل يحوي مسودة قبل الحفظ.
     *
     * @param  array<string, mixed>  $formState
     * @return array{sanitized: array<string, mixed>|null, had_stored_draft: bool}
     */
    public static function persistForRecord(MedicalRecord $record, array $formState): array
    {
        $hadStoredDraft = filled($record->extraction_review_draft);

        $sanitized = self::sanitizeForStorage($formState);
        $record->forceFill([
            'extraction_review_draft' => $sanitized,
        ])->save();

        return [
            'sanitized' => $sanitized,
            'had_stored_draft' => $hadStoredDraft,
        ];
    }
}
