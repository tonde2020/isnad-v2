<?php

namespace App\Models;

use App\Enums\PatientMedicalEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalEvent extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'event_type',
        'event_date',
        'event_time',
        'title',
        'description',
        'source',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => PatientMedicalEventType::class,
            'event_date' => 'date',
            'description' => 'encrypted',
            'metadata' => 'encrypted:array',
        ];
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
