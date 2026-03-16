<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_user_can_view_settings_page(): void
    {
        $user = $this->createFmoUser();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertViewIs('settings.index');
        $response->assertViewHas('settings');
    }

    public function test_settings_page_shows_current_settings(): void
    {
        $user = User::factory()->withSettings([
            'refresh_interval' => 120,
            'notification_sound' => 'bell',
        ])->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $settings = $response->viewData('settings');
        $this->assertEquals(120, $settings['refresh_interval']);
        $this->assertEquals('bell', $settings['notification_sound']);
    }

    public function test_user_can_update_settings(): void
    {
        $user = $this->createFmoUser();

        // Note: Checkboxes that are unchecked are not sent in the request
        // The controller uses $request->has() which returns true if key exists
        $response = $this->actingAs($user)->put('/settings', [
            'refresh_interval' => 90,
            'notification_sound' => 'ping',
            'notify_on_new_request' => '1',  // checked
            // 'notify_on_status_change' omitted = unchecked
            'notify_on_task_assigned' => '1', // checked
        ]);

        $response->assertRedirect('/settings');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(90, $user->getSetting('refresh_interval'));
        $this->assertEquals('ping', $user->getSetting('notification_sound'));
        $this->assertTrue($user->getSetting('notify_on_new_request'));
        $this->assertFalse($user->getSetting('notify_on_status_change'));
    }

    public function test_settings_validation_refresh_interval_min(): void
    {
        $user = $this->createFmoUser();

        $response = $this->actingAs($user)->put('/settings', [
            'refresh_interval' => -1,
            'notification_sound' => 'chime',
        ]);

        $response->assertSessionHasErrors(['refresh_interval']);
    }

    public function test_settings_validation_refresh_interval_max(): void
    {
        $user = $this->createFmoUser();

        $response = $this->actingAs($user)->put('/settings', [
            'refresh_interval' => 601,
            'notification_sound' => 'chime',
        ]);

        $response->assertSessionHasErrors(['refresh_interval']);
    }

    public function test_settings_validation_notification_sound(): void
    {
        $user = $this->createFmoUser();

        $response = $this->actingAs($user)->put('/settings', [
            'refresh_interval' => 60,
            'notification_sound' => 'invalid_sound',
        ]);

        $response->assertSessionHasErrors(['notification_sound']);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/settings');

        $response->assertRedirect('/login');
    }

    // ==================== TASK COUNTS API TESTS ====================

    public function test_fmo_user_gets_task_counts_for_own_requests(): void
    {
        $fmoUser = $this->createFmoUser();
        $this->createPendingRequest($fmoUser);
        $this->createApprovedRequest($fmoUser);
        $this->createCompletedRequest($fmoUser);

        $response = $this->actingAs($fmoUser)->getJson('/api/task-counts');

        $response->assertStatus(200);
        $response->assertJsonStructure(['counts', 'total']);
        $this->assertEquals(1, $response->json('counts.pending'));
        $this->assertEquals(1, $response->json('counts.approved'));
        $this->assertEquals(1, $response->json('counts.completed'));
    }

    public function test_fmo_admin_gets_task_counts_for_all_requests(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $fmoUser = $this->createFmoUser();

        $this->createPendingRequest($fmoUser);
        $this->createPendingRequest($fmoAdmin);
        $this->createApprovedRequest($fmoUser);

        $response = $this->actingAs($fmoAdmin)->getJson('/api/task-counts');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('counts.pending'));
        $this->assertEquals(1, $response->json('counts.approved'));
    }

    public function test_po_admin_gets_task_counts(): void
    {
        $poAdmin = $this->createPoAdmin();
        $this->createApprovedRequest();
        $this->createApprovedRequest();
        $this->createAssignedRequest();

        $response = $this->actingAs($poAdmin)->getJson('/api/task-counts');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('counts.approved'));
    }

    public function test_po_user_gets_task_counts_for_assigned_requests(): void
    {
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();

        $this->createAssignedRequest(null, null, $poUser);
        $this->createInProgressRequest(null, $poUser);
        $this->createAssignedRequest(null, null, $otherPoUser); // Should not count

        $response = $this->actingAs($poUser)->getJson('/api/task-counts');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('counts.assigned'));
        $this->assertEquals(1, $response->json('counts.in_progress'));
    }

    public function test_task_counts_api_requires_authentication(): void
    {
        $response = $this->getJson('/api/task-counts');

        $response->assertStatus(401);
    }
}
