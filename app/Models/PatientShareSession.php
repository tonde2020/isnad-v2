<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientShareSession extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'token',
        'doctor_name',
        'doctor_phone',
        'allowed_sections',
        'allowed_record_ids',
        'expires_at',
        'opened_at',
        'revoked_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allowed_sections' => 'array',
            'allowed_record_ids' => 'array',
            'expires_at' => 'datetime',
            'opened_at' => 'datetime',
            'revoked_at' => 'datetime',
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
