<?php

namespace App\Models;

use App\Enums\PatientDiseaseKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDisease extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'disease_master_id',
        'kind',
        'custom_name',
        'status',
        'diagnosed_at',
        'severity',
        'source',
        'is_confirmed',
        'confirmed_by',
        'confirmed_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => PatientDiseaseKind::class,
            'diagnosed_at' => 'date',
            'is_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
            'custom_name' => 'encrypted',
            'notes' => 'encrypted',
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
     * @return BelongsTo<DiseaseMaster, $this>
     */
    public function diseaseMaster(): BelongsTo
    {
        return $this->belongsTo(DiseaseMaster::class);
    }

    public function displayLabel(): string
    {
        if ($this->disease_master_id && $this->diseaseMaster) {
            return $this->diseaseMaster->name_ar;
        }

        return (string) ($this->custom_name ?: '—');
    }
}
