<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiseaseMaster extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'category',
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

    /**
     * @return HasMany<PatientDisease, $this>
     */
    public function patientDiseases(): HasMany
    {
        return $this->hasMany(PatientDisease::class, 'disease_master_id');
    }
}
