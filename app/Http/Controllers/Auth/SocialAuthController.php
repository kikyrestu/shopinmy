<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        if (!\App\Models\Setting::get('google_login_enabled')) {
            abort(404);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        if (!\App\Models\Setting::get('google_login_enabled')) {
            abort(404);
        }

        try {
            $googleUser = Socialite::driver('google')->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))->user();

            // Find user by google_id or email
            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Update existing user with Google ID
                    $user->update([
                        'google_id' => $googleUser->id,
                        'google_token' => $googleUser->token,
                        'google_refresh_token' => $googleUser->refreshToken,
                    ]);
                } else {
                    // Create a new user
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'google_token' => $googleUser->token,
                        'google_refresh_token' => $googleUser->refreshToken,
                        // Password is not required for google login, but we can generate a random one just in case
                        'password' => bcrypt(Str::random(24)),
                        'email_verified_at' => now(), // Assume Google emails are verified
                    ]);
                }
            }

            Auth::login($user);

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            return redirect()->route('login')->with('status', 'Failed to login with Google: ' . $e->getMessage());
        }
    }
}
