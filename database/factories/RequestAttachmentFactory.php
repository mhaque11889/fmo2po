<?php

namespace Database\Factories;

use App\Models\RequestAttachment;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestAttachment>
 */
class RequestAttachmentFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = RequestAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = Str::uuid();
        $extension = fake()->randomElement(['jpg', 'png', 'pdf']);
        $isImage = in_array($extension, ['jpg', 'png', 'gif', 'jpeg']);

        return [
            'requirement_request_id' => RequirementRequest::factory(),
            'original_filename' => fake()->word() . '.' . $extension,
            'stored_filename' => $uuid . '.' . $extension,
            'file_path' => 'attachments/1/' . $uuid . '.' . $extension,
            'file_type' => $isImage ? 'image' : 'pdf',
            'mime_type' => $isImage ? 'image/' . $extension : 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 5 * 1024 * 1024), // 1KB to 5MB
            'uploaded_by' => User::factory()->fmoUser(),
        ];
    }

    /**
     * Set the request this attachment belongs to.
     */
    public function forRequest(RequirementRequest $request): static
    {
        $uuid = Str::uuid();
        $extension = fake()->randomElement(['jpg', 'png', 'pdf']);

        return $this->state(fn (array $attributes) => [
            'requirement_request_id' => $request->id,
            'file_path' => 'attachments/' . $request->id . '/' . $uuid . '.' . $extension,
        ]);
    }

    /**
     * Set the uploader of the attachment.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Create an image attachment (JPG).
     */
    public function image(): static
    {
        $uuid = Str::uuid();

        return $this->state(fn (array $attributes) => [
            'original_filename' => fake()->word() . '.jpg',
            'stored_filename' => $uuid . '.jpg',
            'file_path' => 'attachments/' . ($attributes['requirement_request_id'] ?? 1) . '/' . $uuid . '.jpg',
            'file_type' => 'image',
            'mime_type' => 'image/jpeg',
        ]);
    }

    /**
     * Create a PNG image attachment.
     */
    public function png(): static
    {
        $uuid = Str::uuid();

        return $this->state(fn (array $attributes) => [
            'original_filename' => fake()->word() . '.png',
            'stored_filename' => $uuid . '.png',
            'file_path' => 'attachments/' . ($attributes['requirement_request_id'] ?? 1) . '/' . $uuid . '.png',
            'file_type' => 'image',
            'mime_type' => 'image/png',
        ]);
    }

    /**
     * Create a GIF image attachment.
     */
    public function gif(): static
    {
        $uuid = Str::uuid();

        return $this->state(fn (array $attributes) => [
            'original_filename' => fake()->word() . '.gif',
            'stored_filename' => $uuid . '.gif',
            'file_path' => 'attachments/' . ($attributes['requirement_request_id'] ?? 1) . '/' . $uuid . '.gif',
            'file_type' => 'image',
            'mime_type' => 'image/gif',
        ]);
    }

    /**
     * Create a PDF attachment.
     */
    public function pdf(): static
    {
        $uuid = Str::uuid();

        return $this->state(fn (array $attributes) => [
            'original_filename' => fake()->word() . '.pdf',
            'stored_filename' => $uuid . '.pdf',
            'file_path' => 'attachments/' . ($attributes['requirement_request_id'] ?? 1) . '/' . $uuid . '.pdf',
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    /**
     * Create an attachment with a specific file size.
     */
    public function withSize(int $bytes): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $bytes,
        ]);
    }

    /**
     * Create a small attachment (< 100KB).
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => fake()->numberBetween(1024, 100 * 1024),
        ]);
    }

    /**
     * Create a large attachment (~5MB).
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => fake()->numberBetween(4 * 1024 * 1024, 5 * 1024 * 1024),
        ]);
    }

    /**
     * Create an attachment with a specific original filename.
     */
    public function withOriginalFilename(string $filename): static
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);

        return $this->state(fn (array $attributes) => [
            'original_filename' => $filename,
            'file_type' => $isImage ? 'image' : 'pdf',
            'mime_type' => $isImage ? 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension) : 'application/pdf',
        ]);
    }
}
