<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone' => fake()->numerify('09########'),
            'national_id' => fake()->optional()->numerify('#############'),
            'birth_date' => fake()->optional()->date(),
            'blood_type' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'chronic_diseases' => fake()->optional()->sentence(),
            'allergies' => fake()->optional()->sentence(),
        ];
    }
}
