<?php

namespace Tests\Traits;

use App\Models\RequestAttachment;
use App\Models\RequestHistory;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait CreatesTestData
{
    /**
     * Create an FMO User.
     */
    protected function createFmoUser(array $attributes = []): User
    {
        return User::factory()->fmoUser()->create($attributes);
    }

    /**
     * Create an FMO Admin.
     */
    protected function createFmoAdmin(array $attributes = []): User
    {
        return User::factory()->fmoAdmin()->create($attributes);
    }

    /**
     * Create a PO Admin.
     */
    protected function createPoAdmin(array $attributes = []): User
    {
        return User::factory()->poAdmin()->create($attributes);
    }

    /**
     * Create a PO User.
     */
    protected function createPoUser(array $attributes = []): User
    {
        return User::factory()->poUser()->create($attributes);
    }

    /**
     * Create a Super Admin.
     */
    protected function createSuperAdmin(array $attributes = []): User
    {
        return User::factory()->superAdmin()->create($attributes);
    }

    /**
     * Create users for each role.
     *
     * @return array<string, User>
     */
    protected function createAllRoleUsers(): array
    {
        return [
            'fmo_user' => $this->createFmoUser(),
            'fmo_admin' => $this->createFmoAdmin(),
            'po_admin' => $this->createPoAdmin(),
            'po_user' => $this->createPoUser(),
            'super_admin' => $this->createSuperAdmin(),
        ];
    }

    /**
     * Create a pending requirement request.
     */
    protected function createPendingRequest(?User $creator = null, array $attributes = []): RequirementRequest
    {
        $creator = $creator ?? $this->createFmoUser();

        return RequirementRequest::factory()
            ->pending()
            ->createdBy($creator)
            ->create($attributes);
    }

    /**
     * Create an approved requirement request.
     */
    protected function createApprovedRequest(?User $creator = null, ?User $approver = null, array $attributes = []): RequirementRequest
    {
        $creator = $creator ?? $this->createFmoUser();
        $approver = $approver ?? $this->createFmoAdmin();

        return RequirementRequest::factory()
            ->createdBy($creator)
            ->approvedBy($approver)
            ->create($attributes);
    }

    /**
     * Create an assigned requirement request.
     */
    protected function createAssignedRequest(
        ?User $creator = null,
        ?User $approver = null,
        ?User $assignee = null,
        ?User $assigner = null,
        array $attributes = []
    ): RequirementRequest {
        $creator = $creator ?? $this->createFmoUser();
        $approver = $approver ?? $this->createFmoAdmin();
        $assignee = $assignee ?? $this->createPoUser();
        $assigner = $assigner ?? $this->createPoAdmin();

        return RequirementRequest::factory()
            ->createdBy($creator)
            ->approvedBy($approver)
            ->assignedTo($assignee, $assigner)
            ->create($attributes);
    }

    /**
     * Create an in-progress requirement request.
     */
    protected function createInProgressRequest(
        ?User $creator = null,
        ?User $assignee = null,
        array $attributes = []
    ): RequirementRequest {
        $creator = $creator ?? $this->createFmoUser();
        $assignee = $assignee ?? $this->createPoUser();

        $request = $this->createAssignedRequest($creator, null, $assignee, null, $attributes);
        $request->update([
            'status' => 'in_progress',
            'progress_remarks' => 'Working on it',
            'progress_at' => now(),
        ]);

        return $request->fresh();
    }

    /**
     * Create a completed requirement request.
     */
    protected function createCompletedRequest(?User $creator = null, array $attributes = []): RequirementRequest
    {
        $creator = $creator ?? $this->createFmoUser();

        return RequirementRequest::factory()
            ->completed()
            ->createdBy($creator)
            ->create($attributes);
    }

    /**
     * Create a rejected requirement request.
     */
    protected function createRejectedRequest(?User $creator = null, array $attributes = []): RequirementRequest
    {
        $creator = $creator ?? $this->createFmoUser();

        return RequirementRequest::factory()
            ->rejected()
            ->createdBy($creator)
            ->create($attributes);
    }

    /**
     * Create a cancelled requirement request.
     */
    protected function createCancelledRequest(?User $creator = null, array $attributes = []): RequirementRequest
    {
        $creator = $creator ?? $this->createFmoUser();

        return RequirementRequest::factory()
            ->cancelled()
            ->createdBy($creator)
            ->create($attributes);
    }

    /**
     * Create requests in all statuses.
     *
     * @return array<string, RequirementRequest>
     */
    protected function createRequestsInAllStatuses(?User $creator = null): array
    {
        $creator = $creator ?? $this->createFmoUser();

        return [
            'pending' => $this->createPendingRequest($creator),
            'approved' => $this->createApprovedRequest($creator),
            'assigned' => $this->createAssignedRequest($creator),
            'in_progress' => $this->createInProgressRequest($creator),
            'completed' => $this->createCompletedRequest($creator),
            'rejected' => $this->createRejectedRequest($creator),
            'cancelled' => $this->createCancelledRequest($creator),
        ];
    }

    /**
     * Create a request attachment.
     */
    protected function createAttachment(
        RequirementRequest $request,
        ?User $uploader = null,
        string $type = 'image'
    ): RequestAttachment {
        $uploader = $uploader ?? $request->creator;

        $factory = RequestAttachment::factory()
            ->forRequest($request)
            ->uploadedBy($uploader);

        return $type === 'pdf' ? $factory->pdf()->create() : $factory->image()->create();
    }

    /**
     * Create history entries for a request.
     */
    protected function createHistoryEntry(
        RequirementRequest $request,
        User $user,
        string $action = 'created',
        ?array $changes = null,
        ?string $remarks = null
    ): RequestHistory {
        return RequestHistory::factory()
            ->forRequest($request)
            ->byUser($user)
            ->state([
                'action' => $action,
                'changes' => $changes,
                'remarks' => $remarks,
            ])
            ->create();
    }

    /**
     * Create a fake uploaded file for testing.
     */
    protected function createFakeUploadedFile(string $type = 'image', int $sizeKb = 100): UploadedFile
    {
        Storage::fake('local');

        return match ($type) {
            'pdf' => UploadedFile::fake()->create('document.pdf', $sizeKb, 'application/pdf'),
            'png' => UploadedFile::fake()->image('photo.png', 100, 100),
            'gif' => UploadedFile::fake()->image('animation.gif', 100, 100),
            default => UploadedFile::fake()->image('photo.jpg', 100, 100),
        };
    }

    /**
     * Create multiple fake uploaded files.
     *
     * @return array<UploadedFile>
     */
    protected function createFakeUploadedFiles(int $count = 2, string $type = 'image'): array
    {
        Storage::fake('local');

        $files = [];
        for ($i = 0; $i < $count; $i++) {
            $files[] = $this->createFakeUploadedFile($type);
        }

        return $files;
    }

    /**
     * Assert that a request has a specific status.
     */
    protected function assertRequestStatus(RequirementRequest $request, string $status): void
    {
        $this->assertEquals($status, $request->fresh()->status);
    }

    /**
     * Assert that a history entry exists for a request.
     */
    protected function assertHistoryRecorded(RequirementRequest $request, string $action, ?User $user = null): void
    {
        $query = RequestHistory::where('requirement_request_id', $request->id)
            ->where('action', $action);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $this->assertTrue($query->exists(), "History entry for action '{$action}' not found.");
    }

    /**
     * Get valid request data for creating a request.
     */
    protected function getValidRequestData(array $overrides = []): array
    {
        return array_merge([
            'item' => 'Test Item',
            'dimensions' => '10x20x30 cm',
            'qty' => 5,
            'location' => 'Building A',
            'remarks' => 'Test remarks',
        ], $overrides);
    }

    /**
     * Get valid user data for creating a user.
     */
    protected function getValidUserData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'fmo_user',
        ], $overrides);
    }
}
