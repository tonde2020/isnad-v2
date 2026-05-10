<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MedicalRecord extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'title',
        'record_type',
        'file_path',
        'enhanced_file_path',
        'file_type',
        'description',
        'record_date',
        'uploaded_at',
        'is_reviewed',
        'reviewed_by',
        'reviewed_at',
        'ocr_text',
        'extracted_entities',
        'extracted_entities_applied_at',
        'extracted_entities_applied_by',
        'extraction_review_draft',
        'ai_summary',
        'processing_status',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'uploaded_at' => 'datetime',
            'is_reviewed' => 'boolean',
            'reviewed_at' => 'datetime',
            'processed_at' => 'datetime',
            'extracted_entities' => 'array',
            'extracted_entities_applied_at' => 'datetime',
            'extraction_review_draft' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MedicalRecord $record): void {
            if ($record->uploaded_at === null) {
                $record->uploaded_at = now();
            }
        });
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * الملف الأصلي كما رُفع (للمقارنة الطبية).
     *
     * @return array{disk: string, path: string}
     */
    public function originalDisplayStorage(): array
    {
        foreach (['medical_private', 'public'] as $diskName) {
            if ($this->file_path && Storage::disk($diskName)->exists($this->file_path)) {
                return ['disk' => $diskName, 'path' => $this->file_path];
            }
        }

        return ['disk' => 'medical_private', 'path' => $this->file_path ?? ''];
    }

    /**
     * مسار العرض الافتراضي للطبيب: نسخة محسّنة إن وُجدت، وإلا الأصل.
     *
     * @return array{disk: string, path: string}
     */
    public function preferredDisplayStorage(): array
    {
        if ($this->enhanced_file_path && Storage::disk('medical_private')->exists($this->enhanced_file_path)) {
            return ['disk' => 'medical_private', 'path' => $this->enhanced_file_path];
        }

        if ($this->file_path && Storage::disk('medical_private')->exists($this->file_path)) {
            return ['disk' => 'medical_private', 'path' => $this->file_path];
        }

        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return ['disk' => 'public', 'path' => $this->file_path];
        }

        return ['disk' => 'medical_private', 'path' => $this->file_path ?? ''];
    }

    public function hasDistinctEnhancedFile(): bool
    {
        return filled($this->enhanced_file_path)
            && filled($this->file_path)
            && $this->enhanced_file_path !== $this->file_path;
    }
}
