<?php

namespace Database\Factories;

use App\Models\RequestHistory;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestHistory>
 */
class RequestHistoryFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = RequestHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requirement_request_id' => RequirementRequest::factory(),
            'user_id' => User::factory(),
            'action' => 'created',
            'changes' => null,
            'remarks' => null,
        ];
    }

    /**
     * Set the request this history entry belongs to.
     */
    public function forRequest(RequirementRequest $request): static
    {
        return $this->state(fn (array $attributes) => [
            'requirement_request_id' => $request->id,
        ]);
    }

    /**
     * Set the user who performed this action.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a "created" history entry.
     */
    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'changes' => null,
            'remarks' => null,
        ]);
    }

    /**
     * Create an "edited" history entry.
     */
    public function edited(array $changes = []): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'edited',
            'changes' => $changes ?: [
                'item' => ['old' => 'Old Item', 'new' => 'New Item'],
            ],
            'remarks' => null,
        ]);
    }

    /**
     * Create an "approved" history entry.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'approved',
            'changes' => null,
            'remarks' => null,
        ]);
    }

    /**
     * Create a "rejected" history entry.
     */
    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'rejected',
            'changes' => null,
            'remarks' => $reason ?? fake()->sentence(),
        ]);
    }

    /**
     * Create a "cancelled" history entry.
     */
    public function cancelled(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'cancelled',
            'changes' => null,
            'remarks' => $reason,
        ]);
    }

    /**
     * Create an "assigned" history entry.
     */
    public function assigned(?string $assigneeName = null): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'assigned',
            'changes' => $assigneeName ? ['assigned_to' => $assigneeName] : null,
            'remarks' => null,
        ]);
    }

    /**
     * Create an "in_progress" history entry.
     */
    public function inProgress(?string $remarks = null): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'in_progress',
            'changes' => null,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Create a "completed" history entry.
     */
    public function completed(?string $remarks = null): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'completed',
            'changes' => null,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Add changes to the history entry.
     */
    public function withChanges(array $changes): static
    {
        return $this->state(fn (array $attributes) => [
            'changes' => $changes,
        ]);
    }

    /**
     * Add remarks to the history entry.
     */
    public function withRemarks(string $remarks): static
    {
        return $this->state(fn (array $attributes) => [
            'remarks' => $remarks,
        ]);
    }
}
