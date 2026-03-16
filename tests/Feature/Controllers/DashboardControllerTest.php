<?php

namespace Tests\Feature\Controllers;

use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_super_admin_sees_super_admin_dashboard(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.super-admin');
        $response->assertViewHas(['pendingRequests', 'approvedRequests', 'assignedRequests', 'poUsers']);
    }

    public function test_fmo_user_sees_fmo_user_dashboard(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.fmo-user');
        $response->assertViewHas(['requests', 'stats']);
    }

    public function test_fmo_admin_sees_fmo_admin_dashboard(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.fmo-admin');
        $response->assertViewHas(['pendingRequests', 'stats']);
    }

    public function test_po_admin_sees_po_admin_dashboard(): void
    {
        $poAdmin = $this->createPoAdmin();

        $response = $this->actingAs($poAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.po-admin');
        $response->assertViewHas(['approvedRequests', 'poUsers', 'stats']);
    }

    public function test_po_user_sees_po_user_dashboard(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.po-user');
        $response->assertViewHas(['assignedRequests', 'stats']);
    }

    public function test_fmo_user_dashboard_shows_own_requests_stats(): void
    {
        $fmoUser = $this->createFmoUser();

        // Create various requests
        $this->createPendingRequest($fmoUser);
        $this->createApprovedRequest($fmoUser);
        $this->createCompletedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->get('/dashboard');

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['pending_on_po']);
        $this->assertEquals(1, $stats['completed']);
    }

    public function test_fmo_admin_dashboard_shows_all_pending_requests(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $fmoUser = $this->createFmoUser();

        // Create pending requests from different users
        $this->createPendingRequest($fmoUser);
        $this->createPendingRequest($fmoAdmin);

        $response = $this->actingAs($fmoAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['pending']);
    }

    public function test_po_admin_dashboard_shows_approved_requests_ready_for_assignment(): void
    {
        $poAdmin = $this->createPoAdmin();
        $fmoUser = $this->createFmoUser();

        $this->createApprovedRequest($fmoUser);
        $this->createApprovedRequest($fmoUser);
        $this->createPendingRequest($fmoUser); // Should not appear

        $response = $this->actingAs($poAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['ready_to_assign']);
    }

    public function test_po_user_dashboard_shows_only_assigned_to_them(): void
    {
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();

        // Create requests assigned to current user
        $this->createAssignedRequest(null, null, $poUser);
        $this->createInProgressRequest(null, $poUser);

        // Create request assigned to another user
        $this->createAssignedRequest(null, null, $otherPoUser);

        $response = $this->actingAs($poUser)->get('/dashboard');

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['assigned']);
        $this->assertEquals(1, $stats['in_progress']);
    }

    public function test_super_admin_dashboard_shows_po_users_for_assignment(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $poUser1 = $this->createPoUser(['name' => 'PO User 1']);
        $poUser2 = $this->createPoUser(['name' => 'PO User 2']);

        $response = $this->actingAs($superAdmin)->get('/dashboard');

        $response->assertStatus(200);
        $poUsers = $response->viewData('poUsers');

        $this->assertEquals(2, $poUsers->count());
    }
}
