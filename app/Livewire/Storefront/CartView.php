<?php

namespace App\Livewire\Storefront;

use Livewire\Component;

class CartView extends Component
{
    public $cart;
    public $recommendedProducts = [];
    public $selectedItems = [];
    public $selectAll = true;
    public $publicVouchers = [];
    public $selectedVoucherId = null;

    public function mount()
    {
        $this->loadCart();
        $this->selectedVoucherId = session('selected_voucher_id', null);
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

        // Initialize selected items if this is the first load
        if ($this->cart && empty($this->selectedItems)) {
            $this->selectedItems = $this->cart->items->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

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
            $this->selectedItems = array_values(array_diff($this->selectedItems, [(string) $itemId, $itemId]));
            $this->loadCart();
            $this->dispatch('cart-updated');
        }
    }

    public function removeSelectedItems()
    {
        if ($this->cart && count($this->selectedItems) > 0) {
            $this->cart->items()->whereIn('id', $this->selectedItems)->delete();
            $this->selectedItems = [];
            $this->selectAll = false;
            $this->loadCart();
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: __('Selected items removed from cart.'));
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = $this->cart->items->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function updatedSelectedItems()
    {
        if ($this->cart) {
            $this->selectAll = count($this->selectedItems) === $this->cart->items->count();
        }
    }

    public function proceedToCheckout()
    {
        if (count($this->selectedItems) > 0) {
            session()->put('checkout_items', $this->selectedItems);
            return redirect()->route('checkout.index');
        } else {
            $this->dispatch('notify', message: __('Please select at least one item to checkout.'));
        }
    }

    public function addToCart($productId)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) return;

        if ($product->stock !== null && $product->stock < 1) {
            $this->dispatch('notify', message: __('Product is out of stock.'));
            return;
        }

        $sessionId = session()->getId();
        $userId = auth()->id();

        // 1. Get or create Cart
        $cart = \App\Models\Cart::firstOrCreate(
            $userId ? ['user_id' => $userId] : ['session_id' => $sessionId]
        );

        // 2. Check if item exists in cart
        $cartItem = $cart->items()->where('product_id', $product->id)->whereNull('variant_id')->first();

        if ($cartItem) {
            // Check stock limit
            if ($product->stock !== null && ($cartItem->qty + 1) > $product->stock) {
                $this->dispatch('notify', message: __('Maximum stock reached.'));
                return;
            }
            $cartItem->increment('qty');
        } else {
            // Determine price
            $price = $product->price;

            $cart->items()->create([
                'product_id' => $product->id,
                'qty' => 1,
                'price' => $price,
            ]);
        }

        $this->loadCart();
        // Add newly added product to selected items if it wasn't there
        if ($cartItem) {
            $itemIdStr = (string)$cartItem->id;
            if (!in_array($itemIdStr, $this->selectedItems)) {
                $this->selectedItems[] = $itemIdStr;
            }
        } else {
            // If it's a new item, we need to load cart again to get the new item ID and select it
            $newItem = $cart->items()->where('product_id', $product->id)->whereNull('variant_id')->first();
            if ($newItem) {
                $this->selectedItems[] = (string)$newItem->id;
            }
        }
        
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: __('Barang berhasil ditambahkan ke keranjang!'));
    }

    public function selectVoucher($id)
    {
        $this->selectedVoucherId = $id;
        session()->put('selected_voucher_id', $id);
        $this->dispatch('notify', message: __('Promo berhasil dipasang!'));
    }

    public function render()
    {
        $subtotal = 0;
        $totalDiscount = 0;
        
        if ($this->cart) {
            foreach ($this->cart->items as $item) {
                if (in_array((string)$item->id, $this->selectedItems) || in_array($item->id, $this->selectedItems)) {
                    $price = $item->effective_price;
                    $basePrice = $item->product->price;
                    $subtotal += ($price * $item->qty);
                    
                    if ($price < $basePrice) {
                        $totalDiscount += (($basePrice - $price) * $item->qty);
                    }
                }
            }
        }

        $this->publicVouchers = \App\Models\Voucher::where('is_active', true)
            ->where('is_public', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->get();

        $activeVoucher = null;
        if ($this->selectedVoucherId) {
            $activeVoucher = $this->publicVouchers->firstWhere('id', $this->selectedVoucherId);
        }

        return view('livewire.storefront.cart-view', compact('subtotal', 'totalDiscount', 'activeVoucher'))
            ->extends('layouts.storefront')
            ->section('content');
    }
}
