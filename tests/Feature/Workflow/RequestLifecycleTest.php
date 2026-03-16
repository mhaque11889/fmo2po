<?php

namespace Tests\Feature\Workflow;

use App\Models\RequestHistory;
use App\Models\RequirementRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RequestLifecycleTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    /**
     * Test the complete happy path workflow:
     * pending -> approved -> assigned -> in_progress -> completed
     */
    public function test_complete_request_workflow_happy_path(): void
    {
        $fmoUser = $this->createFmoUser();
        $fmoAdmin = $this->createFmoAdmin();
        $poAdmin = $this->createPoAdmin();
        $poUser = $this->createPoUser();

        // Step 1: FMO User creates a request
        $response = $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Office Chairs',
            'qty' => 10,
            'location' => 'Conference Room',
            'remarks' => 'Ergonomic chairs needed',
        ]);

        $response->assertRedirect('/dashboard');
        $request = RequirementRequest::first();
        $this->assertEquals('pending', $request->status);
        $this->assertEquals($fmoUser->id, $request->created_by);

        // Step 2: FMO Admin approves the request
        $response = $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/approve");

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($fmoAdmin->id, $request->approved_by);
        $this->assertNotNull($request->approved_at);

        // Step 3: PO Admin assigns to PO User
        $response = $this->actingAs($poAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ]);

        $request->refresh();
        $this->assertEquals('assigned', $request->status);
        $this->assertEquals($poUser->id, $request->assigned_to);
        $this->assertEquals($poAdmin->id, $request->assigned_by);
        $this->assertNotNull($request->assigned_at);

        // Step 4: PO User marks as in progress
        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress", [
            'progress_remarks' => 'Ordering from supplier',
        ]);

        $request->refresh();
        $this->assertEquals('in_progress', $request->status);
        $this->assertEquals('Ordering from supplier', $request->progress_remarks);
        $this->assertNotNull($request->progress_at);

        // Step 5: PO User completes the request
        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/complete", [
            'completion_remarks' => 'Chairs delivered and installed',
        ]);

        $request->refresh();
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('Chairs delivered and installed', $request->completion_remarks);
        $this->assertNotNull($request->completed_at);

        // Verify full history trail
        $history = RequestHistory::where('requirement_request_id', $request->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $this->assertEquals(5, $history->count());
        $this->assertEquals('created', $history[0]->action);
        $this->assertEquals('approved', $history[1]->action);
        $this->assertEquals('assigned', $history[2]->action);
        $this->assertEquals('in_progress', $history[3]->action);
        $this->assertEquals('completed', $history[4]->action);
    }

    /**
     * Test the rejection workflow:
     * pending -> rejected -> deleted
     */
    public function test_rejection_workflow(): void
    {
        $fmoUser = $this->createFmoUser();
        $fmoAdmin = $this->createFmoAdmin();

        // Create request
        $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Invalid Request',
            'qty' => 1,
            'location' => 'Nowhere',
        ]);

        $request = RequirementRequest::first();

        // FMO Admin rejects
        $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/reject");

        $request->refresh();
        $this->assertEquals('rejected', $request->status);

        // Creator can delete rejected request
        $this->actingAs($fmoUser)->delete("/requests/{$request->id}");

        $this->assertDatabaseMissing('requirement_requests', ['id' => $request->id]);
    }

    /**
     * Test the cancellation workflow:
     * pending -> cancelled
     */
    public function test_cancellation_workflow(): void
    {
        $fmoUser = $this->createFmoUser();

        // Create request
        $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Changed Mind Item',
            'qty' => 1,
            'location' => 'Office',
        ]);

        $request = RequirementRequest::first();

        // Creator cancels
        $this->actingAs($fmoUser)->post("/requests/{$request->id}/cancel");

        $request->refresh();
        $this->assertEquals('cancelled', $request->status);

        // Verify history
        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'cancelled',
        ]);
    }

    /**
     * Test that direct completion from assigned status works.
     */
    public function test_direct_completion_from_assigned(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        // PO User can complete directly without marking in progress first
        $this->actingAs($poUser)->post("/requests/{$request->id}/complete", [
            'completion_remarks' => 'Quick job done',
        ]);

        $request->refresh();
        $this->assertEquals('completed', $request->status);
    }

    /**
     * Test that workflow state transitions are enforced.
     */
    public function test_invalid_state_transitions_are_prevented(): void
    {
        $fmoUser = $this->createFmoUser();
        $poUser = $this->createPoUser();

        // Cannot mark pending request in progress (even if assigned_to is set)
        $request = $this->createPendingRequest($fmoUser);
        $request->update(['assigned_to' => $poUser->id]); // Set assignee but wrong status
        $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress")
            ->assertSessionHas('error');

        // Cannot cancel approved request (only pending can be cancelled)
        $approvedRequest = $this->createApprovedRequest($fmoUser);
        $this->actingAs($fmoUser)->post("/requests/{$approvedRequest->id}/cancel")
            ->assertSessionHas('error');

        // Cannot delete non-rejected request
        $pendingRequest = $this->createPendingRequest($fmoUser);
        $this->actingAs($fmoUser)->delete("/requests/{$pendingRequest->id}")
            ->assertSessionHas('error');
    }

    /**
     * Test that only authorized users can perform workflow actions.
     */
    public function test_workflow_authorization(): void
    {
        $fmoUser = $this->createFmoUser();
        $otherFmoUser = $this->createFmoUser();
        $fmoAdmin = $this->createFmoAdmin();
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();

        $request = $this->createPendingRequest($fmoUser);

        // FMO User cannot approve
        $this->actingAs($fmoUser)->post("/requests/{$request->id}/approve")
            ->assertStatus(403);

        // Approve it
        $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/approve");

        // FMO Admin cannot assign
        $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ])->assertStatus(403);

        // PO Admin assigns
        $poAdmin = $this->createPoAdmin();
        $this->actingAs($poAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ]);

        // Other PO user cannot mark in progress
        $this->actingAs($otherPoUser)->post("/requests/{$request->id}/in-progress")
            ->assertStatus(403);

        // Correct PO user can
        $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress")
            ->assertRedirect();
    }

    /**
     * Test edit permissions throughout workflow.
     */
    public function test_edit_permissions_by_status(): void
    {
        $fmoUser = $this->createFmoUser();
        $fmoAdmin = $this->createFmoAdmin();

        // Pending - can edit
        $pendingRequest = $this->createPendingRequest($fmoUser);
        $this->actingAs($fmoUser)->get("/requests/{$pendingRequest->id}/edit")
            ->assertStatus(200);
        $this->actingAs($fmoAdmin)->get("/requests/{$pendingRequest->id}/edit")
            ->assertStatus(200);

        // Approved - cannot edit
        $approvedRequest = $this->createApprovedRequest($fmoUser);
        $this->actingAs($fmoUser)->get("/requests/{$approvedRequest->id}/edit")
            ->assertStatus(403);
        $this->actingAs($fmoAdmin)->get("/requests/{$approvedRequest->id}/edit")
            ->assertStatus(403);

        // Assigned - cannot edit
        $assignedRequest = $this->createAssignedRequest($fmoUser);
        $this->actingAs($fmoUser)->get("/requests/{$assignedRequest->id}/edit")
            ->assertStatus(403);
    }

    /**
     * Test that changes are properly tracked in history.
     */
    public function test_edit_changes_are_tracked(): void
    {
        $fmoUser = $this->createFmoUser();

        // Create request directly without using factory states
        $request = RequirementRequest::create([
            'created_by' => $fmoUser->id,
            'item' => 'Original Item',
            'qty' => 5,
            'location' => 'Building A',
            'status' => 'pending',
        ]);

        // Verify setup
        $this->assertEquals($fmoUser->id, $request->created_by);
        $this->assertEquals('pending', $request->status);
        $this->assertTrue($fmoUser->isFmoUser());

        $response = $this->actingAs($fmoUser)->put("/requests/{$request->id}", [
            'item' => 'Updated Item',
            'qty' => 10,
            'location' => 'Building A', // unchanged
        ]);

        $response->assertRedirect();

        $history = RequestHistory::where('requirement_request_id', $request->id)
            ->where('action', 'edited')
            ->first();

        $this->assertNotNull($history);
        $this->assertArrayHasKey('item', $history->changes);
        $this->assertArrayHasKey('qty', $history->changes);
        $this->assertArrayNotHasKey('location', $history->changes); // unchanged
    }
}
