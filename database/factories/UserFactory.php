<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * The model the factory creates.
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'fmo_user',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an FMO User.
     */
    public function fmoUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'fmo_user',
        ]);
    }

    /**
     * Create an FMO Admin.
     */
    public function fmoAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'fmo_admin',
        ]);
    }

    /**
     * Create a PO Admin.
     */
    public function poAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'po_admin',
        ]);
    }

    /**
     * Create a PO User.
     */
    public function poUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'po_user',
        ]);
    }

    /**
     * Create a Super Admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    /**
     * Create a user with Google OAuth credentials.
     */
    public function withGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'google_id' => fake()->uuid(),
            'avatar' => fake()->imageUrl(100, 100, 'people'),
            'password' => null,
        ]);
    }

    /**
     * Create a user with custom settings.
     */
    public function withSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => $settings,
        ]);
    }
}
