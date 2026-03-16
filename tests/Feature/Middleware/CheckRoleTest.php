<?php

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_middleware_redirects_unauthenticated_users_to_login(): void
    {
        // Access a protected route without authentication
        $response = $this->get('/requests');

        $response->assertRedirect();
    }

    public function test_middleware_allows_user_with_correct_role(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        // /requests requires fmo_admin or super_admin role
        $response = $this->actingAs($fmoAdmin)->get('/requests');

        $response->assertStatus(200);
    }

    public function test_middleware_denies_user_with_incorrect_role(): void
    {
        $fmoUser = $this->createFmoUser();

        // /requests requires fmo_admin or super_admin role
        $response = $this->actingAs($fmoUser)->get('/requests');

        $response->assertStatus(403);
    }

    public function test_middleware_allows_any_of_multiple_roles(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $fmoAdmin = $this->createFmoAdmin();

        // /requests allows both fmo_admin and super_admin
        $response1 = $this->actingAs($superAdmin)->get('/requests');
        $response2 = $this->actingAs($fmoAdmin)->get('/requests');

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    }

    public function test_role_middleware_on_create_request_routes(): void
    {
        $fmoUser = $this->createFmoUser();
        $fmoAdmin = $this->createFmoAdmin();
        $poUser = $this->createPoUser();

        // FMO users and admins can access create
        $this->actingAs($fmoUser)->get('/requests/create')->assertStatus(200);
        $this->actingAs($fmoAdmin)->get('/requests/create')->assertStatus(200);

        // PO users cannot
        $this->actingAs($poUser)->get('/requests/create')->assertStatus(403);
    }

    public function test_role_middleware_on_admin_routes(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $poAdmin = $this->createPoAdmin();
        $fmoUser = $this->createFmoUser();

        // Admins can access user management
        $this->actingAs($fmoAdmin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($poAdmin)->get('/admin/users')->assertStatus(200);

        // Regular users cannot
        $this->actingAs($fmoUser)->get('/admin/users')->assertStatus(403);
    }

    public function test_role_middleware_on_reports_routes(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $poAdmin = $this->createPoAdmin();
        $superAdmin = $this->createSuperAdmin();
        $fmoUser = $this->createFmoUser();

        // Admins can access reports
        $this->actingAs($fmoAdmin)->get('/reports')->assertStatus(200);
        $this->actingAs($poAdmin)->get('/reports')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/reports')->assertStatus(200);

        // Regular users cannot
        $this->actingAs($fmoUser)->get('/reports')->assertStatus(403);
    }

    public function test_role_middleware_on_super_admin_only_routes(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $fmoAdmin = $this->createFmoAdmin();

        // Super admin can access import
        $this->actingAs($superAdmin)->get('/admin/users/import')->assertStatus(200);

        // FMO admin cannot
        $this->actingAs($fmoAdmin)->get('/admin/users/import')->assertStatus(403);
    }
}
