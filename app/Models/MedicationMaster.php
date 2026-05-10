<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationMaster extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'brand_name',
        'generic_name',
        'strength',
        'form',
        'manufacturer',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function displayLabel(): string
    {
        $parts = array_filter([
            $this->brand_name,
            $this->generic_name ? '('.$this->generic_name.')' : null,
            $this->strength,
            $this->form,
        ]);

        return trim(implode(' ', $parts)) ?: ('#'.$this->getKey());
    }

    /**
     * @return HasMany<PatientMedication, $this>
     */
    public function patientMedications(): HasMany
    {
        return $this->hasMany(PatientMedication::class, 'medication_master_id');
    }
}
