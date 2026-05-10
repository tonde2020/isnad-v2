<?php

namespace App\Models;

use App\Enums\PatientDiseaseKind;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'national_id',
        'birth_date',
        'blood_type',
        'gender',
        'state',
        'locality',
        'displacement_area',
        'emergency_contact_name',
        'emergency_contact_phone',
        'chronic_diseases',
        'allergies',
        'ai_summary_disabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'chronic_diseases' => 'encrypted',
            'allergies' => 'encrypted',
            'clinical_ai_summary' => 'encrypted',
            'clinical_ai_summary_generated_at' => 'datetime',
            'ai_summary_disabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Patient $patient): void {
            if (empty($patient->uuid)) {
                $patient->uuid = (string) Str::uuid();
            }
        });

        static::created(function (Patient $patient): void {
            if (filled($patient->registry_number)) {
                return;
            }

            $patient->forceFill([
                'registry_number' => static::generateRegistryNumberForId($patient->getKey()),
            ])->saveQuietly();
        });
    }

    public static function generateRegistryNumberForId(int|string $id): string
    {
        $prefix = config('isnad.registry.prefix', 'ISN');
        $length = max(6, min(12, (int) config('isnad.registry.sequence_length', 8)));

        return $prefix.'-'.str_pad((string) $id, $length, '0', STR_PAD_LEFT);
    }

    public static function normalizeRegistryNumberInput(string $input): string
    {
        return strtoupper(trim(preg_replace('/\s+/u', '', $input)));
    }

    /**
     * علاقات تحميل صفحة الملف العام (عرض للطبيب / استعلام برقم السجل).
     *
     * @return array<int|string, mixed>
     */
    public static function publicProfileWithRelations(): array
    {
        return [
            'patientChronicDiseases.diseaseMaster',
            'patientAllergyRecords.diseaseMaster',
            'patientMedications' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('id'),
            'patientMedications.medicationMaster',
            'medications' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('id'),
            'medicalRecords' => fn ($query) => $query
                ->orderByDesc('record_date')
                ->orderByDesc('id')
                ->limit(15),
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<PatientMedicalEvent, $this>
     */
    public function medicalEvents(): HasMany
    {
        return $this->hasMany(PatientMedicalEvent::class);
    }

    /**
     * @return HasMany<CurrentMedication, $this>
     */
    public function medications(): HasMany
    {
        return $this->hasMany(CurrentMedication::class);
    }

    /**
     * @return HasMany<PatientMedication, $this>
     */
    public function patientMedications(): HasMany
    {
        return $this->hasMany(PatientMedication::class);
    }

    /**
     * @return HasMany<PatientDisease, $this>
     */
    public function patientChronicDiseases(): HasMany
    {
        return $this->hasMany(PatientDisease::class)
            ->where('kind', PatientDiseaseKind::Chronic);
    }

    /**
     * @return HasMany<PatientDisease, $this>
     */
    public function patientAllergyRecords(): HasMany
    {
        return $this->hasMany(PatientDisease::class)
            ->where('kind', PatientDiseaseKind::Allergy);
    }

    /**
     * @return HasMany<MedicalRecord, $this>
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * @return HasMany<PatientAccessLog, $this>
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(PatientAccessLog::class);
    }

    /**
     * @return HasMany<PatientShareSession, $this>
     */
    public function shareSessions(): HasMany
    {
        return $this->hasMany(PatientShareSession::class);
    }

    /**
     * @return HasMany<ClinicalAiAuditLog, $this>
     */
    public function clinicalAiAuditLogs(): HasMany
    {
        return $this->hasMany(ClinicalAiAuditLog::class);
    }
}
