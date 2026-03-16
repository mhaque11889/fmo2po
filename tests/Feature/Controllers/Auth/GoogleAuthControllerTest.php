<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class GoogleAuthControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_redirect_to_google(): void
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn(Mockery::mock([
                'redirect' => redirect('https://accounts.google.com/oauth'),
            ]));

        $response = $this->get('/auth/google');

        $response->assertRedirect();
    }

    public function test_callback_logs_in_existing_user(): void
    {
        $user = $this->createFmoUser([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->email = 'existing@example.com';
        $socialiteUser->id = 'google-id-123';
        $socialiteUser->name = 'Google Name';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn(Mockery::mock([
                'user' => $socialiteUser,
            ]));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Check that Google fields were updated
        $user->refresh();
        $this->assertEquals('google-id-123', $user->google_id);
        $this->assertEquals('https://example.com/avatar.jpg', $user->avatar);
        $this->assertEquals('Google Name', $user->name);
    }

    public function test_callback_denies_unregistered_user(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->email = 'unregistered@example.com';

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn(Mockery::mock([
                'user' => $socialiteUser,
            ]));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_callback_handles_google_exception(): void
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('user')
            ->once()
            ->andThrow(new \Exception('Google OAuth error'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($mock);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_logout_clears_session(): void
    {
        $user = $this->createFmoUser();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = $this->createFmoUser();

        $this->actingAs($user);
        $sessionId = session()->getId();

        $this->post('/logout');

        $this->assertNotEquals($sessionId, session()->getId());
    }

    public function test_welcome_page_shows_google_signin(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
        // Check that Google signin button is visible
        $response->assertSee('Sign in with Google');
    }

    public function test_fake_login_is_forbidden_in_non_local_environment(): void
    {
        // The fake-login route only works in 'local' environment
        // In testing environment (APP_ENV=testing), it should return 403
        $user = $this->createFmoUser();

        $response = $this->post('/auth/fake-login', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(403);
    }
}
