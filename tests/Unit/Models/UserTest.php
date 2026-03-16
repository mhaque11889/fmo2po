<?php

namespace Tests\Unit\Models;

use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class UserTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_user_has_fillable_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'fmo_user',
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('fmo_user', $user->role);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-password',
        ]);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(\Hash::check('plain-password', $user->password));
    }

    public function test_is_fmo_user_returns_true_for_fmo_user(): void
    {
        $user = $this->createFmoUser();

        $this->assertTrue($user->isFmoUser());
        $this->assertFalse($user->isFmoAdmin());
        $this->assertFalse($user->isPoAdmin());
        $this->assertFalse($user->isPoUser());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_fmo_admin_returns_true_for_fmo_admin(): void
    {
        $user = $this->createFmoAdmin();

        $this->assertFalse($user->isFmoUser());
        $this->assertTrue($user->isFmoAdmin());
        $this->assertFalse($user->isPoAdmin());
        $this->assertFalse($user->isPoUser());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_po_admin_returns_true_for_po_admin(): void
    {
        $user = $this->createPoAdmin();

        $this->assertFalse($user->isFmoUser());
        $this->assertFalse($user->isFmoAdmin());
        $this->assertTrue($user->isPoAdmin());
        $this->assertFalse($user->isPoUser());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_po_user_returns_true_for_po_user(): void
    {
        $user = $this->createPoUser();

        $this->assertFalse($user->isFmoUser());
        $this->assertFalse($user->isFmoAdmin());
        $this->assertFalse($user->isPoAdmin());
        $this->assertTrue($user->isPoUser());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_is_super_admin_returns_true_for_super_admin(): void
    {
        $user = $this->createSuperAdmin();

        $this->assertFalse($user->isFmoUser());
        $this->assertFalse($user->isFmoAdmin());
        $this->assertFalse($user->isPoAdmin());
        $this->assertFalse($user->isPoUser());
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_default_settings_returns_expected_structure(): void
    {
        $defaults = User::defaultSettings();

        $this->assertArrayHasKey('refresh_interval', $defaults);
        $this->assertArrayHasKey('notification_sound', $defaults);
        $this->assertArrayHasKey('notify_on_new_request', $defaults);
        $this->assertArrayHasKey('notify_on_status_change', $defaults);
        $this->assertArrayHasKey('notify_on_task_assigned', $defaults);

        $this->assertEquals(60, $defaults['refresh_interval']);
        $this->assertEquals('chime', $defaults['notification_sound']);
        $this->assertTrue($defaults['notify_on_new_request']);
        $this->assertTrue($defaults['notify_on_status_change']);
        $this->assertTrue($defaults['notify_on_task_assigned']);
    }

    public function test_get_setting_returns_user_setting(): void
    {
        $user = User::factory()->withSettings([
            'refresh_interval' => 120,
        ])->create();

        $this->assertEquals(120, $user->getSetting('refresh_interval'));
    }

    public function test_get_setting_returns_default_when_not_set(): void
    {
        $user = User::factory()->create(['settings' => null]);

        $this->assertEquals(60, $user->getSetting('refresh_interval'));
        $this->assertEquals('chime', $user->getSetting('notification_sound'));
    }

    public function test_get_setting_returns_provided_default_for_unknown_key(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('custom', $user->getSetting('unknown_key', 'custom'));
    }

    public function test_get_all_settings_merges_with_defaults(): void
    {
        $user = User::factory()->withSettings([
            'refresh_interval' => 30,
            'custom_setting' => 'value',
        ])->create();

        $settings = $user->getAllSettings();

        $this->assertEquals(30, $settings['refresh_interval']);
        $this->assertEquals('value', $settings['custom_setting']);
        $this->assertEquals('chime', $settings['notification_sound']);
        $this->assertTrue($settings['notify_on_new_request']);
    }

    public function test_update_setting_updates_single_setting(): void
    {
        $user = User::factory()->create(['settings' => null]);

        $user->updateSetting('refresh_interval', 90);

        $this->assertEquals(90, $user->fresh()->getSetting('refresh_interval'));
        $this->assertEquals('chime', $user->fresh()->getSetting('notification_sound'));
    }

    public function test_update_setting_preserves_other_settings(): void
    {
        $user = User::factory()->withSettings([
            'refresh_interval' => 60,
            'notification_sound' => 'bell',
        ])->create();

        $user->updateSetting('refresh_interval', 120);

        $this->assertEquals(120, $user->fresh()->getSetting('refresh_interval'));
        $this->assertEquals('bell', $user->fresh()->getSetting('notification_sound'));
    }

    public function test_user_has_created_requests_relationship(): void
    {
        $user = $this->createFmoUser();
        $request = $this->createPendingRequest($user);

        $this->assertTrue($user->createdRequests->contains($request));
        $this->assertEquals(1, $user->createdRequests()->count());
    }

    public function test_user_has_approved_requests_relationship(): void
    {
        $admin = $this->createFmoAdmin();
        $request = $this->createApprovedRequest(null, $admin);

        $this->assertTrue($admin->approvedRequests->contains($request));
    }

    public function test_user_has_assigned_requests_relationship(): void
    {
        $poUser = $this->createPoUser();
        $request = $this->createAssignedRequest(null, null, $poUser);

        $this->assertTrue($poUser->assignedRequests->contains($request));
    }

    public function test_settings_cast_to_array(): void
    {
        $user = User::factory()->withSettings([
            'key' => 'value',
        ])->create();

        $this->assertIsArray($user->settings);
    }

    public function test_google_user_can_have_null_password(): void
    {
        $user = User::factory()->withGoogle()->create();

        $this->assertNotNull($user->google_id);
        $this->assertNull($user->password);
    }
}
