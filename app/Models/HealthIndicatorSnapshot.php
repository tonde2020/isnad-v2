<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthIndicatorSnapshot extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'snapshot_date',
        'region_key',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'payload' => 'array',
        ];
    }
}
