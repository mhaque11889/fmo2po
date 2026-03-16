<?php

namespace Database\Factories;

use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequirementRequest>
 */
class RequirementRequestFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = RequirementRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->fmoUser(),
            'item' => fake()->words(3, true),
            'dimensions' => fake()->optional()->regexify('[0-9]{1,3}x[0-9]{1,3}x[0-9]{1,3} cm'),
            'qty' => fake()->numberBetween(1, 100),
            'location' => fake()->randomElement(['Building A', 'Building B', 'Building C', 'Main Office', 'Warehouse']),
            'remarks' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }

    /**
     * Set the creator of the request.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Create a pending request.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'assigned_to' => null,
            'assigned_by' => null,
            'assigned_at' => null,
            'progress_remarks' => null,
            'progress_at' => null,
            'completion_remarks' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Create an approved request.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory()->fmoAdmin(),
            'approved_at' => now(),
            'assigned_to' => null,
            'assigned_by' => null,
            'assigned_at' => null,
        ]);
    }

    /**
     * Set the approver of the request.
     */
    public function approvedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Create an assigned request.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'approved_by' => User::factory()->fmoAdmin(),
            'approved_at' => now()->subHour(),
            'assigned_to' => User::factory()->poUser(),
            'assigned_by' => User::factory()->poAdmin(),
            'assigned_at' => now(),
        ]);
    }

    /**
     * Set the assignee of the request.
     */
    public function assignedTo(User $assignee, ?User $assigner = null): static
    {
        return $this->state(function (array $attributes) use ($assignee, $assigner) {
            $state = [
                'status' => 'assigned',
                'assigned_to' => $assignee->id,
                'assigned_at' => now(),
            ];

            if ($assigner) {
                $state['assigned_by'] = $assigner->id;
            }

            return $state;
        });
    }

    /**
     * Create a request that is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'approved_by' => User::factory()->fmoAdmin(),
            'approved_at' => now()->subHours(2),
            'assigned_to' => User::factory()->poUser(),
            'assigned_by' => User::factory()->poAdmin(),
            'assigned_at' => now()->subHour(),
            'progress_remarks' => fake()->sentence(),
            'progress_at' => now(),
        ]);
    }

    /**
     * Create a completed request.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_by' => User::factory()->fmoAdmin(),
            'approved_at' => now()->subHours(3),
            'assigned_to' => User::factory()->poUser(),
            'assigned_by' => User::factory()->poAdmin(),
            'assigned_at' => now()->subHours(2),
            'progress_remarks' => fake()->sentence(),
            'progress_at' => now()->subHour(),
            'completion_remarks' => fake()->sentence(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Create a rejected request.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => User::factory()->fmoAdmin(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Create a cancelled request.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Create a request with specific item details.
     */
    public function withItem(string $item, int $qty = 1, ?string $dimensions = null): static
    {
        return $this->state(fn (array $attributes) => [
            'item' => $item,
            'qty' => $qty,
            'dimensions' => $dimensions,
        ]);
    }

    /**
     * Create a request at a specific location.
     */
    public function atLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
        ]);
    }

    /**
     * Create a request with remarks.
     */
    public function withRemarks(string $remarks): static
    {
        return $this->state(fn (array $attributes) => [
            'remarks' => $remarks,
        ]);
    }
}
