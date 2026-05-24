<?php

namespace App\Livewire\Storefront;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Review;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product;
    public $selectedImage;
    public $qty = 1;
    public $isWishlisted = false;
    
    // For variants grouping (e.g., Color => 'Red', Size => 'XL')
    public $selectedVariants = [];
    
    // Review form
    public $reviewRating = 5;
    public $reviewComment = '';

    public function mount($slug)
    {
        $this->product = Product::with([
            'category', 
            'brand', 
            'productImages', 
            'primaryImage', 
            'variants',
            'reviews.user'
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        // Set initial main image
        if ($this->product->primaryImage) {
            $this->selectedImage = \Illuminate\Support\Facades\Storage::url($this->product->primaryImage->path);
        } elseif ($this->product->productImages->isNotEmpty()) {
            $this->selectedImage = \Illuminate\Support\Facades\Storage::url($this->product->productImages->first()->path);
        } elseif (!empty($this->product->images) && isset($this->product->images[0])) {
            $this->selectedImage = \Illuminate\Support\Facades\Storage::url($this->product->images[0]);
        }

        // Initialize selected variants with first available options
        if ($this->product->variants->isNotEmpty()) {
            $groupedVariants = $this->product->variants->groupBy('name');
            foreach ($groupedVariants as $name => $variants) {
                $this->selectedVariants[$name] = $variants->first()->value;
            }
        }

        // Check wishlist status
        if (auth()->check()) {
            $this->isWishlisted = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->exists();
        }
    }

    public function changeImage($url)
    {
        $this->selectedImage = $url;
    }

    public function getMaxStockProperty()
    {
        $maxStock = $this->product->stock;
        
        $selectedVariantModel = $this->getSelectedVariantModel();
        if ($selectedVariantModel && $selectedVariantModel->stock !== null) {
            $maxStock = $selectedVariantModel->stock;
        }

        return $maxStock;
    }

    public function incrementQty()
    {
        if ($this->maxStock === null || $this->qty < $this->maxStock) {
            $this->qty++;
        }
    }

    public function decrementQty()
    {
        if ($this->qty > 1) {
            $this->qty--;
        }
    }

    public function selectVariant($name, $value)
    {
        $this->selectedVariants[$name] = $value;
        $this->qty = 1; // Reset qty when variant changes
    }

    public function getSelectedVariantModel()
    {
        if (empty($this->selectedVariants)) return null;

        // Find the variant that matches ALL selected attributes.
        // Assuming variant `value` can be a comma-separated list of values (e.g. "Red, XL")
        // Or if it's one-to-one, we just need to ensure the variant's value encompasses the selection.
        // If variants are structured with a single `value` column containing combinations like "Red - L":
        $selectedValues = array_values($this->selectedVariants);
        
        return $this->product->variants->first(function ($variant) use ($selectedValues) {
            // Check if every selected value is present in the variant's value string (Bug-P04: exact word match)
            foreach ($selectedValues as $val) {
                $val = trim($val);
                // Check exact match in comma-separated list first
                $variantValues = array_map('trim', explode(',', $variant->value));
                if (in_array($val, $variantValues, true)) {
                    continue;
                }
                
                // Fallback: word boundary match to prevent "Red" matching "Reddish"
                if (!preg_match('/\b' . preg_quote($val, '/') . '\b/i', $variant->value)) {
                    return false;
                }
            }
            return true;
        });
    }

    public function addToCart()
    {
        $variant = $this->getSelectedVariantModel();
        
        // Prevent adding to cart if product requires variant but none valid is selected
        if ($this->product->variants->isNotEmpty() && !$variant) {
            $this->dispatch('notify', message: __('Please select a valid variant combination.'));
            return;
        }
        
        // Stock Check
        $maxStock = $this->maxStock;
        if ($maxStock !== null && $this->qty > $maxStock) { // Bug-P03: Handle null stock
            $this->dispatch('notify', message: __('Insufficient stock available.'));
            return;
        }

        $sessionId = session()->getId();
        $userId = auth()->id();

        // Find or create cart
        $cart = \App\Models\Cart::firstOrCreate(
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId],
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId]
        );

        $variantId = $variant?->id;

        // Check if item already exists in cart
        $cartItem = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('product_id', $this->product->id)
            ->where('variant_id', $variantId)
            ->first();

        if ($cartItem) {
            if (($cartItem->qty + $this->qty) > $maxStock) {
                $this->dispatch('notify', message: __('Maximum stock reached in cart.'));
                return;
            }
            $cartItem->increment('qty', $this->qty);
        } else {
            \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $this->product->id,
                'variant_id' => $variantId,
                'qty' => $this->qty,
            ]);
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: __('Added to cart successfully!'));
    }

    public function buyNow()
    {
        $variant = $this->getSelectedVariantModel();
        
        // Prevent adding to cart if product requires variant but none valid is selected
        if ($this->product->variants->isNotEmpty() && !$variant) {
            $this->dispatch('notify', message: __('Please select a valid variant combination.'));
            return;
        }
        
        // Stock Check
        $maxStock = $this->maxStock;
        if ($maxStock !== null && $this->qty > $maxStock) {
            $this->dispatch('notify', message: __('Insufficient stock available.'));
            return;
        }

        $sessionId = session()->getId();
        $userId = auth()->id();

        // Find or create cart
        $cart = \App\Models\Cart::firstOrCreate(
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId],
            ['user_id' => $userId, 'session_id' => $userId ? null : $sessionId]
        );

        $variantId = $variant?->id;

        // Check if item already exists in cart
        $cartItem = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('product_id', $this->product->id)
            ->where('variant_id', $variantId)
            ->first();

        if ($cartItem) {
            if (($cartItem->qty + $this->qty) > $maxStock) {
                $this->dispatch('notify', message: __('Maximum stock reached in cart.'));
                return;
            }
            $cartItem->increment('qty', $this->qty);
        } else {
            $cartItem = \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $this->product->id,
                'variant_id' => $variantId,
                'qty' => $this->qty,
            ]);
        }

        // Set the session for partial checkout with this specific item
        session(['checkout_items' => [$cartItem->id]]);
        
        // Redirect directly to checkout
        return redirect()->route('checkout');
    }

    public function toggleWishlist()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->isWishlisted = false;
            $this->dispatch('notify', message: __('Removed from wishlist.'));
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $this->product->id,
            ]);
            $this->isWishlisted = true;
            $this->dispatch('notify', message: __('Added to wishlist!'));
        }
    }

    public function getHasPurchasedProperty()
    {
        if (!auth()->check()) return false;

        return \App\Models\Order::where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'delivered'])
            ->whereHas('items', function ($query) {
                $query->where('product_id', $this->product->id);
            })->exists();
    }

    public function submitReview()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!$this->hasPurchased) {
            $this->dispatch('notify', message: __('Anda harus membeli produk ini terlebih dahulu untuk memberikan ulasan.'));
            return;
        }

        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewComment' => 'required|string|min:10|max:1000',
        ]);

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->first();

        if ($existingReview) {
            $this->dispatch('notify', message: __('You have already reviewed this product.'));
            return;
        }

        $completedOrder = \App\Models\Order::where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'delivered'])
            ->whereHas('items', function ($query) {
                $query->where('product_id', $this->product->id);
            })->latest()->first();

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'order_id' => $completedOrder?->id,
            'rating' => $this->reviewRating,
            'comment' => $this->reviewComment,
        ]);

        // Reload product to update review counts
        $this->product->load('reviews.user');
        $this->product->loadAvg('reviews', 'rating');
        $this->product->loadCount('reviews');

        $this->reviewComment = '';
        $this->reviewRating = 5;
        $this->dispatch('notify', message: __('Review submitted successfully!'));
    }

    public function render()
    {
        // Calculate dynamic price based on selected variant and flash sale
        $currentPrice = $this->product->active_price;
        $originalPrice = $this->product->price;
        
        $variant = $this->getSelectedVariantModel();
        if ($variant && $variant->price_modifier) {
            $currentPrice += $variant->price_modifier;
            $originalPrice += $variant->price_modifier;
        }

        // Related Products
        $relatedProducts = Product::with('primaryImage')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('livewire.storefront.product-detail', compact('currentPrice', 'originalPrice', 'relatedProducts'))
            ->extends('layouts.storefront')
            ->section('content');
    }
}
