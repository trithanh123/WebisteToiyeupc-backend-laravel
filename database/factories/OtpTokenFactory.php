<?php
namespace Database\Factories;
use App\Models\OtpToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class OtpTokenFactory extends Factory
{
    protected $model = OtpToken::class;
    public function definition(): array
    {
        return [
            'identifier' => fake()->safeEmail(),
            'token'      => fake()->numerify('######'), 
            'expires_at' => now()->addMinutes(10), 
        ];
    }
    public function daHetHan(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => now()->subMinutes(5), 
            ];
        });
    }
}
