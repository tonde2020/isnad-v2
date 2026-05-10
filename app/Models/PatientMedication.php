<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedication extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'medication_master_id',
        'custom_medication_name',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'start_date',
        'stopped_at',
        'stop_reason',
        'source',
        'is_confirmed',
        'confirmed_by',
        'confirmed_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'stopped_at' => 'date',
            'is_active' => 'boolean',
            'is_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
            'custom_medication_name' => 'encrypted',
            'dosage' => 'encrypted',
            'frequency' => 'encrypted',
            'duration' => 'encrypted',
            'instructions' => 'encrypted',
            'stop_reason' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<MedicationMaster, $this>
     */
    public function medicationMaster(): BelongsTo
    {
        return $this->belongsTo(MedicationMaster::class);
    }

    public function displayMedicationName(): string
    {
        if ($this->medication_master_id && $this->medicationMaster) {
            return $this->medicationMaster->displayLabel();
        }

        return (string) ($this->custom_medication_name ?: '—');
    }
}
