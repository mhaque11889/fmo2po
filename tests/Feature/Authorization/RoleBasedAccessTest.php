<?php

namespace Tests\Feature\Authorization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    /**
     * Test that each role can only access routes they are authorized for.
     */
    public function test_fmo_user_access_permissions(): void
    {
        $fmoUser = $this->createFmoUser();
        $ownRequest = $this->createPendingRequest($fmoUser);
        $otherRequest = $this->createPendingRequest();

        // Can access
        $this->actingAs($fmoUser)->get('/dashboard')->assertStatus(200);
        $this->actingAs($fmoUser)->get('/settings')->assertStatus(200);
        $this->actingAs($fmoUser)->get('/requests/create')->assertStatus(200);
        $this->actingAs($fmoUser)->get('/my-requests')->assertStatus(200);
        $this->actingAs($fmoUser)->get("/requests/{$ownRequest->id}")->assertStatus(200);

        // Cannot access
        $this->actingAs($fmoUser)->get('/requests')->assertStatus(403);
        $this->actingAs($fmoUser)->get('/my-assigned')->assertStatus(403);
        $this->actingAs($fmoUser)->get('/reports')->assertStatus(403);
        $this->actingAs($fmoUser)->get('/admin/users')->assertStatus(403);
        $this->actingAs($fmoUser)->get("/requests/{$otherRequest->id}")->assertStatus(403);
    }

    public function test_fmo_admin_access_permissions(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $anyRequest = $this->createPendingRequest();

        // Can access
        $this->actingAs($fmoAdmin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/settings')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/requests/create')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/my-requests')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/requests')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/reports')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get("/requests/{$anyRequest->id}")->assertStatus(200);

        // Cannot access
        $this->actingAs($fmoAdmin)->get('/my-assigned')->assertStatus(403);
        $this->actingAs($fmoAdmin)->get('/admin/users/import')->assertStatus(403);
    }

    public function test_po_admin_access_permissions(): void
    {
        $poAdmin = $this->createPoAdmin();
        $pendingRequest = $this->createPendingRequest();
        $approvedRequest = $this->createApprovedRequest();

        // Can access
        $this->actingAs($poAdmin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($poAdmin)->get('/settings')->assertStatus(200);
        $this->actingAs($poAdmin)->get('/my-assigned')->assertStatus(200);
        $this->actingAs($poAdmin)->get('/reports')->assertStatus(200);
        $this->actingAs($poAdmin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($poAdmin)->get("/requests/{$approvedRequest->id}")->assertStatus(200);

        // Cannot access
        $this->actingAs($poAdmin)->get('/requests/create')->assertStatus(403);
        $this->actingAs($poAdmin)->get('/my-requests')->assertStatus(403);
        $this->actingAs($poAdmin)->get('/requests')->assertStatus(403);
        $this->actingAs($poAdmin)->get('/admin/users/import')->assertStatus(403);
        $this->actingAs($poAdmin)->get("/requests/{$pendingRequest->id}")->assertStatus(403);
    }

    public function test_po_user_access_permissions(): void
    {
        $poUser = $this->createPoUser();
        $assignedRequest = $this->createAssignedRequest(null, null, $poUser);
        $unassignedRequest = $this->createAssignedRequest();

        // Can access
        $this->actingAs($poUser)->get('/dashboard')->assertStatus(200);
        $this->actingAs($poUser)->get('/settings')->assertStatus(200);
        $this->actingAs($poUser)->get('/my-assigned')->assertStatus(200);
        $this->actingAs($poUser)->get("/requests/{$assignedRequest->id}")->assertStatus(200);

        // Cannot access
        $this->actingAs($poUser)->get('/requests/create')->assertStatus(403);
        $this->actingAs($poUser)->get('/my-requests')->assertStatus(403);
        $this->actingAs($poUser)->get('/requests')->assertStatus(403);
        $this->actingAs($poUser)->get('/reports')->assertStatus(403);
        $this->actingAs($poUser)->get('/admin/users')->assertStatus(403);
        $this->actingAs($poUser)->get("/requests/{$unassignedRequest->id}")->assertStatus(403);
    }

    public function test_super_admin_has_full_access(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $anyRequest = $this->createPendingRequest();

        // Super admin can access everything
        $this->actingAs($superAdmin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/settings')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/requests/create')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/my-requests')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/my-assigned')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/requests')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/reports')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/admin/users/import')->assertStatus(200);
        $this->actingAs($superAdmin)->get("/requests/{$anyRequest->id}")->assertStatus(200);
    }
}
