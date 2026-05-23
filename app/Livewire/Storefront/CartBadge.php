<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Livewire\Attributes\On;

class CartBadge extends Component
{
    public $count = 0;

    public function mount()
    {
        $this->updateCount();
    }

    #[On('cart-updated')]
    public function updateCount()
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $cart = \App\Models\Cart::where(function ($q) use ($userId, $sessionId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('session_id', $sessionId)->whereNull('user_id');
            }
        })->first();

        $this->count = $cart ? $cart->items()->sum('qty') : 0;
    }

    public function render()
    {
        return view('livewire.storefront.cart-badge');
    }
}
