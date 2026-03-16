<?php

namespace Tests\Unit\Models;

use App\Models\RequestAttachment;
use App\Models\RequestHistory;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RequirementRequestTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_request_has_fillable_attributes(): void
    {
        $user = $this->createFmoUser();
        $request = RequirementRequest::factory()->createdBy($user)->create([
            'item' => 'Test Item',
            'dimensions' => '10x20x30 cm',
            'qty' => 5,
            'location' => 'Building A',
            'remarks' => 'Test remarks',
        ]);

        $this->assertEquals('Test Item', $request->item);
        $this->assertEquals('10x20x30 cm', $request->dimensions);
        $this->assertEquals(5, $request->qty);
        $this->assertEquals('Building A', $request->location);
        $this->assertEquals('Test remarks', $request->remarks);
    }

    public function test_is_pending_returns_true_for_pending_status(): void
    {
        $request = $this->createPendingRequest();

        $this->assertTrue($request->isPending());
        $this->assertFalse($request->isApproved());
        $this->assertFalse($request->isAssigned());
        $this->assertFalse($request->isInProgress());
        $this->assertFalse($request->isCompleted());
        $this->assertFalse($request->isRejected());
        $this->assertFalse($request->isCancelled());
    }

    public function test_is_approved_returns_true_for_approved_status(): void
    {
        $request = $this->createApprovedRequest();

        $this->assertFalse($request->isPending());
        $this->assertTrue($request->isApproved());
    }

    public function test_is_assigned_returns_true_for_assigned_status(): void
    {
        $request = $this->createAssignedRequest();

        $this->assertFalse($request->isPending());
        $this->assertFalse($request->isApproved());
        $this->assertTrue($request->isAssigned());
    }

    public function test_is_in_progress_returns_true_for_in_progress_status(): void
    {
        $request = $this->createInProgressRequest();

        $this->assertTrue($request->isInProgress());
    }

    public function test_is_completed_returns_true_for_completed_status(): void
    {
        $request = $this->createCompletedRequest();

        $this->assertTrue($request->isCompleted());
    }

    public function test_is_rejected_returns_true_for_rejected_status(): void
    {
        $request = $this->createRejectedRequest();

        $this->assertTrue($request->isRejected());
    }

    public function test_is_cancelled_returns_true_for_cancelled_status(): void
    {
        $request = $this->createCancelledRequest();

        $this->assertTrue($request->isCancelled());
    }

    public function test_can_be_edited_by_creator_returns_true_for_pending(): void
    {
        $request = $this->createPendingRequest();

        $this->assertTrue($request->canBeEditedByCreator());
    }

    public function test_can_be_edited_by_creator_returns_false_for_non_pending(): void
    {
        $approvedRequest = $this->createApprovedRequest();
        $assignedRequest = $this->createAssignedRequest();
        $completedRequest = $this->createCompletedRequest();

        $this->assertFalse($approvedRequest->canBeEditedByCreator());
        $this->assertFalse($assignedRequest->canBeEditedByCreator());
        $this->assertFalse($completedRequest->canBeEditedByCreator());
    }

    public function test_can_be_edited_by_fmo_admin_returns_true_for_pending(): void
    {
        $request = $this->createPendingRequest();

        $this->assertTrue($request->canBeEditedByFmoAdmin());
    }

    public function test_can_be_edited_by_fmo_admin_returns_false_for_non_pending(): void
    {
        $approvedRequest = $this->createApprovedRequest();

        $this->assertFalse($approvedRequest->canBeEditedByFmoAdmin());
    }

    public function test_request_has_creator_relationship(): void
    {
        $user = $this->createFmoUser();
        $request = $this->createPendingRequest($user);

        $this->assertInstanceOf(User::class, $request->creator);
        $this->assertEquals($user->id, $request->creator->id);
    }

    public function test_request_has_approver_relationship(): void
    {
        $approver = $this->createFmoAdmin();
        $request = $this->createApprovedRequest(null, $approver);

        $this->assertInstanceOf(User::class, $request->approver);
        $this->assertEquals($approver->id, $request->approver->id);
    }

    public function test_request_has_assignee_relationship(): void
    {
        $assignee = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $assignee);

        $this->assertInstanceOf(User::class, $request->assignee);
        $this->assertEquals($assignee->id, $request->assignee->id);
    }

    public function test_request_has_assigner_relationship(): void
    {
        $assigner = $this->createPoAdmin();
        $request = $this->createAssignedRequest(null, null, null, $assigner);

        $this->assertInstanceOf(User::class, $request->assigner);
        $this->assertEquals($assigner->id, $request->assigner->id);
    }

    public function test_request_has_history_relationship(): void
    {
        $request = $this->createPendingRequest();
        $history = $this->createHistoryEntry($request, $request->creator, 'created');

        $this->assertTrue($request->history->contains($history));
    }

    public function test_history_is_ordered_by_created_at_descending(): void
    {
        $request = $this->createPendingRequest();
        $older = $this->createHistoryEntry($request, $request->creator, 'created');
        $older->update(['created_at' => now()->subHour()]);

        $newer = $this->createHistoryEntry($request, $request->creator, 'edited');
        $newer->update(['created_at' => now()]);

        $history = $request->fresh()->history;

        $this->assertEquals($newer->id, $history->first()->id);
        $this->assertEquals($older->id, $history->last()->id);
    }

    public function test_request_has_attachments_relationship(): void
    {
        $request = $this->createPendingRequest();
        $attachment = $this->createAttachment($request);

        $this->assertTrue($request->attachments->contains($attachment));
    }

    public function test_log_history_creates_history_entry(): void
    {
        $user = $this->createFmoUser();
        $request = $this->createPendingRequest($user);

        RequirementRequest::logHistory(
            $request->id,
            $user->id,
            'test_action',
            ['field' => 'value'],
            'Test remarks'
        );

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'test_action',
            'remarks' => 'Test remarks',
        ]);
    }

    public function test_datetime_fields_are_cast(): void
    {
        $request = $this->createCompletedRequest();

        $this->assertInstanceOf(\Carbon\Carbon::class, $request->approved_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $request->assigned_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $request->progress_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $request->completed_at);
    }

    public function test_request_factory_creates_request_with_default_pending_status(): void
    {
        $request = RequirementRequest::factory()->create();

        $this->assertEquals('pending', $request->status);
    }

    public function test_approved_factory_state_sets_approved_fields(): void
    {
        $request = RequirementRequest::factory()->approved()->create();

        $this->assertEquals('approved', $request->status);
        $this->assertNotNull($request->approved_by);
        $this->assertNotNull($request->approved_at);
    }

    public function test_assigned_factory_state_sets_assigned_fields(): void
    {
        $request = RequirementRequest::factory()->assigned()->create();

        $this->assertEquals('assigned', $request->status);
        $this->assertNotNull($request->assigned_to);
        $this->assertNotNull($request->assigned_by);
        $this->assertNotNull($request->assigned_at);
    }

    public function test_completed_factory_state_sets_all_workflow_fields(): void
    {
        $request = RequirementRequest::factory()->completed()->create();

        $this->assertEquals('completed', $request->status);
        $this->assertNotNull($request->approved_at);
        $this->assertNotNull($request->assigned_at);
        $this->assertNotNull($request->progress_at);
        $this->assertNotNull($request->completed_at);
    }
}
