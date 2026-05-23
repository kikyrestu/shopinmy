<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $oldSessionId = $request->session()->getId();

        $request->authenticate();
        $request->session()->regenerate();

        $userId = Auth::id();

        // Merge guest cart to user cart
        $guestCart = \App\Models\Cart::where('session_id', $oldSessionId)->whereNull('user_id')->first();
        if ($guestCart) {
            $userCart = \App\Models\Cart::firstOrCreate(['user_id' => $userId]);
            
            foreach ($guestCart->items as $guestItem) {
                $existingItem = \App\Models\CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('variant_id', $guestItem->variant_id)
                    ->first();
                    
                if ($existingItem) {
                    $existingItem->increment('qty', $guestItem->qty);
                } else {
                    $guestItem->update(['cart_id' => $userCart->id]);
                }
            }
            $guestCart->delete();
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
