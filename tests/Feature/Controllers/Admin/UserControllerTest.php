<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    // ==================== INDEX TESTS ====================

    public function test_super_admin_can_view_all_users(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $fmoUser = $this->createFmoUser();
        $poUser = $this->createPoUser();

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $users = $response->viewData('users');
        // Verify our created users are included
        $this->assertTrue($users->pluck('id')->contains($superAdmin->id));
        $this->assertTrue($users->pluck('id')->contains($fmoUser->id));
        $this->assertTrue($users->pluck('id')->contains($poUser->id));
    }

    public function test_fmo_admin_sees_only_fmo_users(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createFmoUser();
        $this->createFmoUser();
        $this->createPoUser(); // Should not appear

        $response = $this->actingAs($fmoAdmin)->get('/admin/users');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertEquals(3, $users->total()); // fmo_admin + 2 fmo_users
    }

    public function test_po_admin_sees_only_po_users(): void
    {
        $poAdmin = $this->createPoAdmin();
        $this->createPoUser();
        $this->createPoUser();
        $this->createFmoUser(); // Should not appear

        $response = $this->actingAs($poAdmin)->get('/admin/users');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertEquals(3, $users->total()); // po_admin + 2 po_users
    }

    public function test_fmo_user_cannot_access_user_management(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_po_user_cannot_access_user_management(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->get('/admin/users');

        $response->assertStatus(403);
    }

    // ==================== CREATE TESTS ====================

    public function test_admin_can_access_create_user_form(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
        $response->assertViewHas('allowedRoles');
    }

    public function test_super_admin_sees_all_roles_in_create_form(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/users/create');

        $allowedRoles = $response->viewData('allowedRoles');
        $this->assertContains('super_admin', $allowedRoles);
        $this->assertContains('fmo_admin', $allowedRoles);
        $this->assertContains('fmo_user', $allowedRoles);
        $this->assertContains('po_admin', $allowedRoles);
        $this->assertContains('po_user', $allowedRoles);
    }

    public function test_fmo_admin_only_sees_fmo_roles(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->get('/admin/users/create');

        $allowedRoles = $response->viewData('allowedRoles');
        $this->assertContains('fmo_admin', $allowedRoles);
        $this->assertContains('fmo_user', $allowedRoles);
        $this->assertNotContains('po_admin', $allowedRoles);
        $this->assertNotContains('po_user', $allowedRoles);
        $this->assertNotContains('super_admin', $allowedRoles);
    }

    // ==================== STORE TESTS ====================

    public function test_admin_can_create_user(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'fmo_user',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'fmo_user',
        ]);
    }

    public function test_fmo_admin_cannot_create_po_user(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->post('/admin/users', [
            'name' => 'New PO User',
            'email' => 'pouser@example.com',
            'role' => 'po_user',
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $existingUser = $this->createFmoUser(['email' => 'existing@example.com']);

        $response = $this->actingAs($fmoAdmin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'role' => 'fmo_user',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_create_user_validates_required_fields(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->post('/admin/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    // ==================== EDIT TESTS ====================

    public function test_admin_can_access_edit_user_form(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $user = $this->createFmoUser();

        $response = $this->actingAs($fmoAdmin)->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
    }

    public function test_fmo_admin_cannot_edit_po_user(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $poUser = $this->createPoUser();

        $response = $this->actingAs($fmoAdmin)->get("/admin/users/{$poUser->id}/edit");

        $response->assertStatus(403);
    }

    public function test_super_admin_can_edit_any_user(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $poUser = $this->createPoUser();

        $response = $this->actingAs($superAdmin)->get("/admin/users/{$poUser->id}/edit");

        $response->assertStatus(200);
    }

    // ==================== UPDATE TESTS ====================

    public function test_admin_can_update_user(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $user = $this->createFmoUser();

        $response = $this->actingAs($fmoAdmin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'fmo_user',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_fmo_admin_cannot_update_po_user(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $poUser = $this->createPoUser();

        $response = $this->actingAs($fmoAdmin)->put("/admin/users/{$poUser->id}", [
            'name' => 'Updated',
            'email' => 'updated@example.com',
            'role' => 'po_user',
        ]);

        $response->assertStatus(403);
    }

    public function test_update_validates_unique_email_except_self(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $user1 = $this->createFmoUser(['email' => 'user1@example.com']);
        $user2 = $this->createFmoUser(['email' => 'user2@example.com']);

        $response = $this->actingAs($fmoAdmin)->put("/admin/users/{$user1->id}", [
            'name' => 'Updated',
            'email' => 'user2@example.com', // Try to use user2's email
            'role' => 'fmo_user',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_allows_same_email(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $user = $this->createFmoUser(['email' => 'same@example.com']);

        $response = $this->actingAs($fmoAdmin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'same@example.com',
            'role' => 'fmo_user',
        ]);

        $response->assertRedirect('/admin/users');
    }

    // ==================== UPDATE ROLE TESTS ====================

    public function test_admin_can_update_user_role(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createFmoUser();

        $response = $this->actingAs($superAdmin)->patch("/admin/users/{$user->id}/role", [
            'role' => 'fmo_admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'fmo_admin',
        ]);
    }

    public function test_fmo_admin_cannot_change_role_to_po(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $user = $this->createFmoUser();

        $response = $this->actingAs($fmoAdmin)->patch("/admin/users/{$user->id}/role", [
            'role' => 'po_admin',
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    // ==================== DELETE ALL USERS TESTS ====================

    public function test_super_admin_can_delete_all_users(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $this->createFmoUser();
        $this->createFmoUser();
        $this->createPoUser();

        $response = $this->actingAs($superAdmin)->delete('/admin/users/delete-all');

        $response->assertRedirect();
        $this->assertEquals(1, User::count()); // Only super admin remains
    }

    public function test_delete_all_users_keeps_current_super_admin(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $otherAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->delete('/admin/users/delete-all');

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
        $this->assertDatabaseMissing('users', ['id' => $otherAdmin->id]);
    }

    public function test_non_super_admin_cannot_delete_all_users(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->delete('/admin/users/delete-all');

        $response->assertStatus(403);
    }

    // ==================== DELETE ALL REQUESTS TESTS ====================

    public function test_super_admin_can_delete_all_requests(): void
    {
        Storage::fake('local');
        $superAdmin = $this->createSuperAdmin();
        $request = $this->createPendingRequest();
        $this->createHistoryEntry($request, $request->creator);
        $this->createAttachment($request, $request->creator);

        $response = $this->actingAs($superAdmin)->delete('/admin/requests/delete-all');

        $response->assertRedirect();
        $this->assertEquals(0, RequirementRequest::count());
        $this->assertDatabaseCount('request_history', 0);
        $this->assertDatabaseCount('request_attachments', 0);
    }

    public function test_non_super_admin_cannot_delete_all_requests(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->delete('/admin/requests/delete-all');

        $response->assertStatus(403);
    }

    // ==================== IMPORT TESTS ====================

    public function test_super_admin_can_access_import_page(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/users/import');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.import');
    }

    public function test_non_super_admin_cannot_access_import_page(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->get('/admin/users/import');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_import_users_from_csv(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $csv = "firstname,lastname,email,role\n";
        $csv .= "John,Doe,john.doe@example.com,fmo_user\n";
        $csv .= "Jane,Smith,jane.smith@example.com,po_user\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $response = $this->actingAs($superAdmin)->post('/admin/users/import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'role' => 'fmo_user',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'po_user',
        ]);
    }

    public function test_import_skips_invalid_rows(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $csv = "firstname,lastname,email,role\n";
        $csv .= "John,Doe,invalid-email,fmo_user\n"; // Invalid email
        $csv .= "Jane,Smith,jane@example.com,invalid_role\n"; // Invalid role
        $csv .= "Valid,User,valid@example.com,fmo_user\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $response = $this->actingAs($superAdmin)->post('/admin/users/import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'valid@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid-email']);
    }

    public function test_import_skips_duplicate_emails(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $existingUser = $this->createFmoUser(['email' => 'existing@example.com']);

        $csv = "firstname,lastname,email,role\n";
        $csv .= "John,Doe,existing@example.com,fmo_user\n";
        $csv .= "Jane,Smith,new@example.com,fmo_user\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $response = $this->actingAs($superAdmin)->post('/admin/users/import', [
            'csv_file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    public function test_import_validates_file_type(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $file = UploadedFile::fake()->create('users.pdf', 100, 'application/pdf');

        $response = $this->actingAs($superAdmin)->post('/admin/users/import', [
            'csv_file' => $file,
        ]);

        $response->assertSessionHasErrors(['csv_file']);
    }

    // ==================== DOWNLOAD TEMPLATE TESTS ====================

    public function test_super_admin_can_download_template(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/users/import/template');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('users_import_template.csv', $response->headers->get('Content-Disposition'));
    }

    public function test_template_contains_example_rows(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/users/import/template');

        $content = $response->getContent();
        $this->assertStringContainsString('firstname,lastname,email,role', $content);
        $this->assertStringContainsString('fmo_user', $content);
        $this->assertStringContainsString('po_user', $content);
    }

    public function test_non_super_admin_cannot_download_template(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->get('/admin/users/import/template');

        $response->assertStatus(403);
    }
}
