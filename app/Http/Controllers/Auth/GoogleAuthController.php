<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Unable to authenticate with Google. Please try again.');
        }

        // Check if user with this email exists in our database
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Access denied. Your email is not registered in the system. Please contact an administrator.');
        }

        // Update Google-specific fields (first time or if changed)
        $user->update([
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
            'name' => $googleUser->name, // Keep name in sync with Google
        ]);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    }
}
