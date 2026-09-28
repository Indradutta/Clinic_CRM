<?php

namespace Database\Factories;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailVerificationCode>
 */
class EmailVerificationCodeFactory extends Factory
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
            'purpose' => EmailVerificationCode::PURPOSE_LOGIN,
            'code_hash' => bcrypt('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'consumed_at' => null,
        ];
    }
}
