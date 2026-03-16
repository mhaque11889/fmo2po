<?php

namespace Tests\Feature\Controllers;

use App\Models\RequestHistory;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RequirementRequestControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    // ==================== INDEX TESTS ====================

    public function test_fmo_admin_can_view_all_requests(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createPendingRequest();
        $this->createApprovedRequest();

        $response = $this->actingAs($fmoAdmin)->get('/requests');

        $response->assertStatus(200);
        $response->assertViewIs('requests.index');
        $response->assertViewHas('requests');
    }

    public function test_super_admin_can_view_all_requests(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/requests');

        $response->assertStatus(200);
    }

    public function test_fmo_user_cannot_view_all_requests(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/requests');

        $response->assertStatus(403);
    }

    public function test_po_user_cannot_view_all_requests(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->get('/requests');

        $response->assertStatus(403);
    }

    // ==================== MY REQUESTS TESTS ====================

    public function test_fmo_user_can_view_own_requests(): void
    {
        $fmoUser = $this->createFmoUser();
        $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->get('/my-requests');

        $response->assertStatus(200);
        $response->assertViewIs('requests.my-requests');
    }

    public function test_fmo_user_can_filter_own_requests_by_status(): void
    {
        $fmoUser = $this->createFmoUser();
        $this->createPendingRequest($fmoUser);
        $this->createApprovedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->get('/my-requests/pending');

        $response->assertStatus(200);
        $requests = $response->viewData('requests');
        $this->assertEquals(1, $requests->count());
    }

    public function test_po_user_cannot_access_my_requests(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->get('/my-requests');

        $response->assertStatus(403);
    }

    // ==================== MY ASSIGNED REQUESTS TESTS ====================

    public function test_po_user_can_view_assigned_requests(): void
    {
        $poUser = $this->createPoUser();
        $this->createAssignedRequest(null, null, $poUser);

        $response = $this->actingAs($poUser)->get('/my-assigned');

        $response->assertStatus(200);
        $response->assertViewIs('requests.my-assigned');
    }

    public function test_po_user_can_filter_assigned_requests_by_status(): void
    {
        $poUser = $this->createPoUser();
        $this->createAssignedRequest(null, null, $poUser);
        $this->createInProgressRequest(null, $poUser);

        $response = $this->actingAs($poUser)->get('/my-assigned/assigned');

        $response->assertStatus(200);
        $requests = $response->viewData('requests');
        $this->assertEquals(1, $requests->count());
    }

    public function test_fmo_user_cannot_access_my_assigned_requests(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/my-assigned');

        $response->assertStatus(403);
    }

    // ==================== CREATE TESTS ====================

    public function test_fmo_user_can_access_create_form(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/requests/create');

        $response->assertStatus(200);
        $response->assertViewIs('requests.create');
    }

    public function test_po_user_cannot_access_create_form(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->get('/requests/create');

        $response->assertStatus(403);
    }

    // ==================== STORE TESTS ====================

    public function test_fmo_user_can_create_request(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Test Item',
            'dimensions' => '10x20x30',
            'qty' => 5,
            'location' => 'Building A',
            'remarks' => 'Test remarks',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('requirement_requests', [
            'item' => 'Test Item',
            'created_by' => $fmoUser->id,
            'status' => 'pending',
        ]);
    }

    public function test_fmo_user_can_create_request_with_attachments(): void
    {
        Storage::fake('local');
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Test Item',
            'qty' => 1,
            'location' => 'Building A',
            'attachments' => [
                UploadedFile::fake()->image('photo.jpg', 100, 100),
            ],
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('request_attachments', [
            'original_filename' => 'photo.jpg',
            'uploaded_by' => $fmoUser->id,
        ]);
    }

    public function test_create_request_logs_history(): void
    {
        $fmoUser = $this->createFmoUser();

        $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Test Item',
            'qty' => 1,
            'location' => 'Building A',
        ]);

        $request = RequirementRequest::first();
        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'user_id' => $fmoUser->id,
            'action' => 'created',
        ]);
    }

    public function test_create_request_validates_required_fields(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->post('/requests', []);

        $response->assertSessionHasErrors(['item', 'qty', 'location']);
    }

    public function test_create_request_validates_qty_min(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Test',
            'qty' => 0,
            'location' => 'Building A',
        ]);

        $response->assertSessionHasErrors(['qty']);
    }

    public function test_create_request_validates_max_attachments(): void
    {
        Storage::fake('local');
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Test',
            'qty' => 1,
            'location' => 'Building A',
            'attachments' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
                UploadedFile::fake()->image('photo3.jpg'),
            ],
        ]);

        $response->assertSessionHasErrors(['attachments']);
    }

    public function test_create_request_validates_attachment_types(): void
    {
        Storage::fake('local');
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->post('/requests', [
            'item' => 'Test',
            'qty' => 1,
            'location' => 'Building A',
            'attachments' => [
                UploadedFile::fake()->create('document.exe', 100),
            ],
        ]);

        $response->assertSessionHasErrors(['attachments.0']);
    }

    public function test_create_request_ajax_returns_json(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('/requests', [
                'item' => 'Test Item',
                'qty' => 1,
                'location' => 'Building A',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Request submitted successfully.',
        ]);
    }

    public function test_po_user_cannot_create_request(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->post('/requests', [
            'item' => 'Test',
            'qty' => 1,
            'location' => 'Building A',
        ]);

        $response->assertStatus(403);
    }

    // ==================== SHOW TESTS ====================

    public function test_fmo_user_can_view_own_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->get("/requests/{$request->id}");

        $response->assertStatus(200);
        $response->assertViewIs('requests.show');
    }

    public function test_fmo_user_cannot_view_others_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $otherUser = $this->createFmoUser();
        $request = $this->createPendingRequest($otherUser);

        $response = $this->actingAs($fmoUser)->get("/requests/{$request->id}");

        $response->assertStatus(403);
    }

    public function test_fmo_admin_can_view_any_request(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoAdmin)->get("/requests/{$request->id}");

        $response->assertStatus(200);
    }

    public function test_po_admin_can_view_approved_request(): void
    {
        $poAdmin = $this->createPoAdmin();
        $request = $this->createApprovedRequest();

        $response = $this->actingAs($poAdmin)->get("/requests/{$request->id}");

        $response->assertStatus(200);
    }

    public function test_po_admin_cannot_view_pending_request(): void
    {
        $poAdmin = $this->createPoAdmin();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($poAdmin)->get("/requests/{$request->id}");

        $response->assertStatus(403);
    }

    public function test_po_user_can_view_assigned_request(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        $response = $this->actingAs($poUser)->get("/requests/{$request->id}");

        $response->assertStatus(200);
    }

    public function test_po_user_cannot_view_request_assigned_to_others(): void
    {
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $otherPoUser);

        $response = $this->actingAs($poUser)->get("/requests/{$request->id}");

        $response->assertStatus(403);
    }

    // ==================== EDIT TESTS ====================

    public function test_fmo_user_can_edit_own_pending_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->get("/requests/{$request->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('requests.edit');
    }

    public function test_fmo_user_cannot_edit_approved_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createApprovedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->get("/requests/{$request->id}/edit");

        $response->assertStatus(403);
    }

    public function test_fmo_user_cannot_edit_others_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $otherUser = $this->createFmoUser();
        $request = $this->createPendingRequest($otherUser);

        $response = $this->actingAs($fmoUser)->get("/requests/{$request->id}/edit");

        $response->assertStatus(403);
    }

    public function test_fmo_admin_can_edit_any_pending_request(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoAdmin)->get("/requests/{$request->id}/edit");

        $response->assertStatus(200);
    }

    // ==================== UPDATE TESTS ====================

    public function test_fmo_user_can_update_own_pending_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->put("/requests/{$request->id}", [
            'item' => 'Updated Item',
            'qty' => 10,
            'location' => 'Building B',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('requirement_requests', [
            'id' => $request->id,
            'item' => 'Updated Item',
            'qty' => 10,
        ]);
    }

    public function test_update_logs_changes_in_history(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser, ['item' => 'Original Item']);

        $this->actingAs($fmoUser)->put("/requests/{$request->id}", [
            'item' => 'Updated Item',
            'qty' => $request->qty,
            'location' => $request->location,
        ]);

        $history = RequestHistory::where('requirement_request_id', $request->id)
            ->where('action', 'edited')
            ->first();

        $this->assertNotNull($history);
        $this->assertArrayHasKey('item', $history->changes);
        $this->assertEquals('Original Item', $history->changes['item']['old']);
        $this->assertEquals('Updated Item', $history->changes['item']['new']);
    }

    public function test_update_without_changes_does_not_create_history(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser, [
            'item' => 'Same Item',
            'dimensions' => '10x20x30',
            'qty' => 5,
            'location' => 'Building A',
            'remarks' => 'Same remarks',
        ]);

        // Submit the exact same values - should not create history
        $this->actingAs($fmoUser)->put("/requests/{$request->id}", [
            'item' => 'Same Item',
            'dimensions' => '10x20x30',
            'qty' => 5,
            'location' => 'Building A',
            'remarks' => 'Same remarks',
        ]);

        $editedHistory = RequestHistory::where('requirement_request_id', $request->id)
            ->where('action', 'edited')
            ->count();

        $this->assertEquals(0, $editedHistory);
    }

    // ==================== APPROVE TESTS ====================

    public function test_fmo_admin_can_approve_pending_request(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('requirement_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $fmoAdmin->id,
        ]);
    }

    public function test_approve_logs_history(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $request = $this->createPendingRequest();

        $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/approve");

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'approved',
            'user_id' => $fmoAdmin->id,
        ]);
    }

    public function test_super_admin_can_approve_request(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($superAdmin)->post("/requests/{$request->id}/approve");

        $response->assertRedirect();
        $this->assertRequestStatus($request, 'approved');
    }

    public function test_fmo_user_cannot_approve_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($fmoUser)->post("/requests/{$request->id}/approve");

        $response->assertStatus(403);
    }

    public function test_approve_ajax_returns_json(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($fmoAdmin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post("/requests/{$request->id}/approve");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // ==================== REJECT TESTS ====================

    public function test_fmo_admin_can_reject_pending_request(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/reject");

        $response->assertRedirect();
        $this->assertDatabaseHas('requirement_requests', [
            'id' => $request->id,
            'status' => 'rejected',
        ]);
    }

    public function test_reject_logs_history(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $request = $this->createPendingRequest();

        $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/reject");

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'rejected',
        ]);
    }

    public function test_fmo_user_cannot_reject_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest();

        $response = $this->actingAs($fmoUser)->post("/requests/{$request->id}/reject");

        $response->assertStatus(403);
    }

    // ==================== ASSIGN TESTS ====================

    public function test_po_admin_can_assign_approved_request(): void
    {
        $poAdmin = $this->createPoAdmin();
        $poUser = $this->createPoUser();
        $request = $this->createApprovedRequest();

        $response = $this->actingAs($poAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('requirement_requests', [
            'id' => $request->id,
            'status' => 'assigned',
            'assigned_to' => $poUser->id,
            'assigned_by' => $poAdmin->id,
        ]);
    }

    public function test_assign_logs_history(): void
    {
        $poAdmin = $this->createPoAdmin();
        $poUser = $this->createPoUser();
        $request = $this->createApprovedRequest();

        $this->actingAs($poAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ]);

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'assigned',
        ]);
    }

    public function test_super_admin_can_assign_request(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $poUser = $this->createPoUser();
        $request = $this->createApprovedRequest();

        $response = $this->actingAs($superAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ]);

        $response->assertRedirect();
        $this->assertRequestStatus($request, 'assigned');
    }

    public function test_fmo_admin_cannot_assign_request(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $poUser = $this->createPoUser();
        $request = $this->createApprovedRequest();

        $response = $this->actingAs($fmoAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poUser->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_assign_to_non_po_user(): void
    {
        $poAdmin = $this->createPoAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createApprovedRequest();

        $response = $this->actingAs($poAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $fmoUser->id,
        ]);

        // Controller uses flash message ('error') not validation errors
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_po_admin_can_self_assign(): void
    {
        $poAdmin = $this->createPoAdmin();
        $request = $this->createApprovedRequest();

        $response = $this->actingAs($poAdmin)->post("/requests/{$request->id}/assign", [
            'assigned_to' => $poAdmin->id,
        ]);

        $response->assertRedirect();
        $this->assertRequestStatus($request, 'assigned');
    }

    // ==================== MARK IN PROGRESS TESTS ====================

    public function test_assignee_can_mark_request_in_progress(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress", [
            'progress_remarks' => 'Working on it',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('requirement_requests', [
            'id' => $request->id,
            'status' => 'in_progress',
            'progress_remarks' => 'Working on it',
        ]);
    }

    public function test_mark_in_progress_logs_history(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress");

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'in_progress',
        ]);
    }

    public function test_non_assignee_cannot_mark_in_progress(): void
    {
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $otherPoUser);

        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress");

        $response->assertStatus(403);
    }

    public function test_cannot_mark_non_assigned_request_in_progress(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createApprovedRequest();
        $request->update(['assigned_to' => $poUser->id]); // Set assignee but wrong status

        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/in-progress");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ==================== COMPLETE TESTS ====================

    public function test_assignee_can_complete_assigned_request(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/complete", [
            'completion_remarks' => 'Done',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('requirement_requests', [
            'id' => $request->id,
            'status' => 'completed',
            'completion_remarks' => 'Done',
        ]);
    }

    public function test_assignee_can_complete_in_progress_request(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createInProgressRequest(null, $poUser);

        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/complete");

        $response->assertRedirect();
        $this->assertRequestStatus($request, 'completed');
    }

    public function test_complete_logs_history(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        $this->actingAs($poUser)->post("/requests/{$request->id}/complete");

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'completed',
        ]);
    }

    public function test_non_assignee_cannot_complete_request(): void
    {
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $otherPoUser);

        $response = $this->actingAs($poUser)->post("/requests/{$request->id}/complete");

        $response->assertStatus(403);
    }

    // ==================== CANCEL TESTS ====================

    public function test_creator_can_cancel_pending_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->post("/requests/{$request->id}/cancel");

        $response->assertRedirect('/dashboard');
        $this->assertRequestStatus($request, 'cancelled');
    }

    public function test_cancel_logs_history(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $this->actingAs($fmoUser)->post("/requests/{$request->id}/cancel");

        $this->assertDatabaseHas('request_history', [
            'requirement_request_id' => $request->id,
            'action' => 'cancelled',
        ]);
    }

    public function test_creator_cannot_cancel_approved_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createApprovedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->post("/requests/{$request->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_non_creator_cannot_cancel_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $otherUser = $this->createFmoUser();
        $request = $this->createPendingRequest($otherUser);

        $response = $this->actingAs($fmoUser)->post("/requests/{$request->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_cancel_ajax_returns_json(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post("/requests/{$request->id}/cancel");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // ==================== DESTROY TESTS ====================

    public function test_creator_can_delete_rejected_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createRejectedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->delete("/requests/{$request->id}");

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('requirement_requests', ['id' => $request->id]);
    }

    public function test_delete_removes_attachments(): void
    {
        Storage::fake('local');
        $fmoUser = $this->createFmoUser();
        $request = $this->createRejectedRequest($fmoUser);
        $attachment = $this->createAttachment($request, $fmoUser);

        $this->actingAs($fmoUser)->delete("/requests/{$request->id}");

        $this->assertDatabaseMissing('request_attachments', ['id' => $attachment->id]);
    }

    public function test_delete_removes_history(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createRejectedRequest($fmoUser);
        $this->createHistoryEntry($request, $fmoUser);

        $this->actingAs($fmoUser)->delete("/requests/{$request->id}");

        $this->assertDatabaseMissing('request_history', ['requirement_request_id' => $request->id]);
    }

    public function test_creator_cannot_delete_non_rejected_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->delete("/requests/{$request->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_non_creator_cannot_delete_request(): void
    {
        $fmoUser = $this->createFmoUser();
        $otherUser = $this->createFmoUser();
        $request = $this->createRejectedRequest($otherUser);

        $response = $this->actingAs($fmoUser)->delete("/requests/{$request->id}");

        $response->assertStatus(403);
    }

    public function test_delete_ajax_returns_json(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createRejectedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->delete("/requests/{$request->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
