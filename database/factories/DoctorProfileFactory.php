<?php

namespace Database\Factories;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorProfile>
 */
class DoctorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialty' => fake()->jobTitle(),
            'education' => fake()->sentence(3),
            'license_number' => fake()->unique()->bothify('MED-########'),
            'years_experience' => fake()->numberBetween(1, 35),
            'address' => fake()->address(),
            'bio' => fake()->paragraph(),
            'consultation_fee' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
