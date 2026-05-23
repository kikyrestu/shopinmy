<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;
use App\Models\Wishlist;

class WishlistPage extends Component
{
    public function removeFromWishlist($wishlistId)
    {
        Wishlist::where('id', $wishlistId)
            ->where('user_id', auth()->id())
            ->delete();

        $this->dispatch('notify', message: __('Removed from wishlist.'));
    }

    public function addToCart($productId)
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $cart = \App\Models\Cart::firstOrCreate(
            ['user_id' => $userId, 'session_id' => null],
            ['user_id' => $userId, 'session_id' => null]
        );

        $existing = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->whereNull('variant_id')
            ->first();

        if ($existing) {
            $existing->increment('qty');
        } else {
            \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => null,
                'qty' => 1,
            ]);
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: __('Added to cart successfully!'));
    }

    public function render()
    {
        $wishlists = Wishlist::where('user_id', auth()->id())
            ->with('product.primaryImage')
            ->latest()
            ->get();

        return view('livewire.storefront.dashboard.wishlist', compact('wishlists'))
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
