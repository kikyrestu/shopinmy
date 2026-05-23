<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Process referral
        if ($request->filled('referral_code') && \App\Models\Setting::get('referral_enabled', true)) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                // Create referral record
                \App\Models\Referral::create([
                    'referrer_id' => $referrer->id,
                    'referee_id' => $user->id,
                    'code' => $request->referral_code,
                    'reward_given' => true,
                ]);

                // Award points to referrer
                $rewardPoints = (int) \App\Models\Setting::get('referral_reward_points', 50);
                if ($rewardPoints > 0) {
                    \App\Models\LoyaltyPoint::create([
                        'user_id' => $referrer->id,
                        'points' => $rewardPoints,
                        'type' => 'referral',
                        'ref_id' => $user->id,
                        'description' => 'Referral bonus for inviting ' . $user->name,
                    ]);
                }
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
