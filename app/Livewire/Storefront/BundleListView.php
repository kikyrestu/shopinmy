<?php

namespace App\Livewire\Storefront;

use App\Models\Bundle;
use App\Models\Cart;
use App\Models\CartItem;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class BundleListView extends Component
{
    public function render()
    {
        $bundles = Bundle::with(['products.primaryImage', 'products.variants'])
            ->where('is_active', true)
            ->get();

        return view('livewire.storefront.bundle-list-view', compact('bundles'))
            ->extends('layouts.storefront')
            ->section('content');
    }

    public function addToCart($bundleId)
    {
        $bundle = Bundle::with('products')->findOrFail($bundleId);
        
        $sessionId = session()->getId();
        $userId = auth()->id();

        $cart = Cart::firstOrCreate(
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId],
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId]
        );

        // Bug-M05: Check existing bundle
        $existingBundleItem = CartItem::where('cart_id', $cart->id)
            ->where('bundle_id', $bundle->id)
            ->exists();
            
        if ($existingBundleItem) {
            $this->dispatch('notify', message: __('Bundle is already in your cart.'));
            return;
        }

        // Bug-M04: Stock check
        foreach ($bundle->products as $product) {
            if ($product->stock !== null && $product->stock < $product->pivot->qty) {
                $this->dispatch('notify', message: "{$product->name} is out of stock.");
                return;
            }
        }

        DB::transaction(function () use ($bundle, $cart) {
            // Calculate proportional price distribution for the bundle
            $totalOriginalPrice = 0;
            foreach ($bundle->products as $product) {
                $totalOriginalPrice += ($product->price * $product->pivot->qty);
            }

            foreach ($bundle->products as $product) {
                // How much percentage this product contributes to the total original price
                $proportion = 0;
                if ($totalOriginalPrice > 0) {
                    $proportion = ($product->price * $product->pivot->qty) / $totalOriginalPrice;
                }
                
                // Note: We'll store the proportion/calculated price directly in CartItem if we added a `price` column, 
                // but since CartItem doesn't have a price column (it relies on Product price), 
                // we will handle the calculation dynamically in the CartView by grouping by bundle_id.
                // We just need to add the items tagged with the bundle_id.

                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'variant_id' => null, // For simplicity, bundles assume default variant or none
                    'qty' => $product->pivot->qty,
                    'bundle_id' => $bundle->id,
                ]);
            }
        });

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: __('Bundle added to cart successfully!'));
    }
}
