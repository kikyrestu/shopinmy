<?php

namespace App\Livewire\Storefront;

use Livewire\Component;

class CartView extends Component
{
    public $cart;
    public $recommendedProducts = [];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $this->cart = \App\Models\Cart::with(['items.product.primaryImage', 'items.variant'])
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId)->whereNull('user_id');
                }
            })->first();

        // Load Cross-Selling Recommendations
        if ($this->cart && $this->cart->items->count() > 0) {
            $categoryIds = $this->cart->items->pluck('product.category_id')->unique()->filter();
            $cartProductIds = $this->cart->items->pluck('product_id')->unique();
            
            $this->recommendedProducts = \App\Models\Product::with(['category', 'primaryImage'])
                ->whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $cartProductIds)
                ->where('is_active', true)
                ->withAvg('reviews', 'rating')
                ->withSum('orderItems as order_items_sum_qty', 'qty')
                ->inRandomOrder()
                ->take(4)
                ->get();
        } else {
            $this->recommendedProducts = collect();
        }
    }

    public function increment($itemId)
    {
        if ($this->cart) {
            $item = $this->cart->items()->with(['product', 'variant'])->find($itemId);
            if ($item) {
                if (!$item->product) {
                    $item->delete();
                    $this->loadCart();
                    return;
                }
                // Check stock logic
                $productStock = $item->product->stock;
                $variantStock = $item->variant?->stock;
                
                $maxStock = $variantStock ?? $productStock;
                
                if ($maxStock === null || $item->qty < $maxStock) {
                    $item->increment('qty');
                    $this->loadCart();
                    $this->dispatch('cart-updated');
                } else {
                    $this->dispatch('notify', message: __('Maximum stock reached.'));
                }
            }
        }
    }

    public function decrement($itemId)
    {
        if ($this->cart) {
            $item = $this->cart->items()->with('product')->find($itemId);
            if ($item) {
                if (!$item->product) {
                    $item->delete();
                    $this->loadCart();
                    return;
                }
                if ($item->qty > 1) {
                    $item->decrement('qty');
                    $this->loadCart();
                    $this->dispatch('cart-updated');
                }
            }
        }
    }

    public function removeItem($itemId)
    {
        if ($this->cart) {
            $this->cart->items()->where('id', $itemId)->delete();
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        $subtotal = 0;
        
        if ($this->cart) {
            foreach ($this->cart->items as $item) {
                $price = $item->effective_price;
                $subtotal += ($price * $item->qty);
            }
        }

        return view('livewire.storefront.cart-view', compact('subtotal'))
            ->extends('layouts.storefront')
            ->section('content');
    }
}
