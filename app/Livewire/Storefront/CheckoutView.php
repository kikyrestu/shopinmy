<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Str;

class CheckoutView extends Component
{
    public $cart;
    
    // User Details
    public $name;
    public $email;
    public $phone;
    
    // Shipping Address
    public $address_line;
    public $city;
    public $state;
    public $postcode;
    
    // Address Book
    public $userAddresses = [];
    public $selectedAddressId = null;
    public $showNewAddressForm = false;
    
    // Payment
    public $payment_method = null; // billplz, stripe, cod, manual_transfer
    
    // Shipping
    public $courier = null;
    public $availableCouriers = [];
    public $isCalculatingShipping = false;
    public $shippingError = null;

    public $subtotal = 0;
    public $shippingCost = 0; // Dynamic rate
    
    // Voucher
    public $voucherCode = '';
    public $voucherId = null;
    public $discountAmount = 0;
    
    public function mount()
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $this->cart = Cart::with(['items.product', 'items.variant'])
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId)->whereNull('user_id');
                }
            })->first();
            
        if (!$this->cart || $this->cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        // Calculate subtotal
        foreach ($this->cart->items as $item) {
            $price = $item->effective_price;
            $this->subtotal += ($price * $item->qty);
        }

        // Pre-fill user data if logged in
        if (auth()->check()) {
            $user = auth()->user();
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';

            // Load user addresses
            $this->userAddresses = \App\Models\Address::where('user_id', $user->id)
                ->orderByDesc('is_default')
                ->get();

            if ($this->userAddresses->isNotEmpty()) {
                // Select default address
                $defaultAddress = $this->userAddresses->first();
                $this->selectedAddressId = $defaultAddress->id;
                
                $this->address_line = $defaultAddress->address;
                $this->city = $defaultAddress->city;
                $this->state = $defaultAddress->state;
                $this->postcode = $defaultAddress->postcode;

                if (strlen(trim($this->postcode)) >= 5) {
                    $this->calculateShipping();
                }
            } else {
                $this->showNewAddressForm = true;
            }
        } else {
            $this->showNewAddressForm = true;
        }
    }

    public function updatedSelectedAddressId($value)
    {
        if ($value && auth()->check()) {
            $address = \App\Models\Address::where('id', $value)
                ->where('user_id', auth()->id())
                ->first();

            if ($address) {
                $this->address_line = $address->address;
                $this->city = $address->city;
                $this->state = $address->state;
                $this->postcode = $address->postcode;
                $this->showNewAddressForm = false;
                
                if (strlen(trim($this->postcode)) >= 5) {
                    $this->calculateShipping();
                }
            }
        }
    }

    public function updatedPostcode($value)
    {
        if (strlen(trim($value)) >= 5) {
            $this->calculateShipping();
        } else {
            $this->availableCouriers = [];
            $this->shippingCost = 0;
            $this->courier = null;
            $this->shippingError = null;
        }
    }

    // Bug-18: Recalculate on city/state changes too
    public function updatedCity() { if (strlen(trim($this->postcode ?? '')) >= 5) $this->calculateShipping(); }
    public function updatedState() { if (strlen(trim($this->postcode ?? '')) >= 5) $this->calculateShipping(); }

    public function updatedCourier($value)
    {
        if ($value && isset($this->availableCouriers[$value])) {
            $this->shippingCost = $this->availableCouriers[$value]['price'];
        }
        if ($this->voucherId) {
            $this->applyVoucher(); // Bug-M01: Recalculate free_shipping
        }
    }

    public function updateShipping()
    {
        if (strlen(trim($this->postcode ?? '')) >= 5) {
            $this->calculateShipping();
        }
    }

    private function validatePaymentMethodEnabled(): void
    {
        $method = $this->payment_method;
        
        $enabledChecks = [
            'billplz'         => \App\Models\Setting::get('billplz_api_key') && \App\Models\Setting::isEnabled('billplz_enabled'),
            'stripe'          => \App\Models\Setting::get('stripe_publishable_key') && \App\Models\Setting::isEnabled('stripe_enabled'),
            'cod'             => \App\Models\Setting::isEnabled('cod_enabled'),
            'manual_transfer' => \App\Models\Setting::isEnabled('manual_transfer_enabled'),
        ];
        
        if (!($enabledChecks[$method] ?? false)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran ini tidak tersedia.'
            ]);
        }
    }

    public function calculateShipping()
    {
        $this->isCalculatingShipping = true;
        $this->shippingError = null;
        
        try {
            $storePostcode = \App\Models\Setting::get('store_postcode');
            
            if (!$storePostcode) {
                $this->shippingError = 'Store origin postcode is not configured.';
                $this->isCalculatingShipping = false;
                return;
            }

            // Calculate total weight
            $totalWeight = 0;
            foreach ($this->cart->items as $item) {
                $weight = $item->product->weight ?? 0.500;
                $totalWeight += ($weight * $item->qty);
            }

            // Fallback if weight is zero
            if ($totalWeight <= 0) $totalWeight = 0.500;

            $myParcel = app(\App\Services\MyParcelService::class);
            $rates = $myParcel->checkPrice($storePostcode, $this->postcode, $totalWeight);
            
            // Reformat rates to a friendly array
            $this->availableCouriers = [];
            
            $prices = $rates['prices'] ?? (isset($rates[0]) ? $rates : []);
            
            if (!empty($prices) && is_array($prices)) {
                foreach ($prices as $rate) {
                    // check_price returns provider_label and effective_price/normal_price
                    if (isset($rate['provider_code'], $rate['provider_label'])) {
                        $price = $rate['effective_price'] ?? $rate['normal_price'] ?? 0;
                        $this->availableCouriers[$rate['provider_code']] = [
                            'name' => $rate['provider_label'],
                            'price' => (float) $price,
                        ];
                    }
                }
            } else {
                $this->shippingError = 'No shipping couriers available for this postcode.';
            }

            if (!empty($this->availableCouriers)) {
                // Auto-select first available or default from settings
                $defaultProvider = \App\Models\Setting::get('myparcel_default_provider');
                if ($defaultProvider && isset($this->availableCouriers[$defaultProvider])) {
                    $this->courier = $defaultProvider;
                } else {
                    $firstCourier = array_key_first($this->availableCouriers);
                    $this->courier = $firstCourier;
                }
                
                $this->shippingCost = $this->availableCouriers[$this->courier]['price'];
            }

        } catch (\Exception $e) {
            $this->shippingError = 'Failed to fetch rates: ' . $e->getMessage();
            $this->availableCouriers = [];
        }

        $this->isCalculatingShipping = false;
        
        // Recalculate voucher if one is active, in case shipping cost changed
        if ($this->voucherId) {
            $this->applyVoucher();
        }
    }

    public function applyVoucher()
    {
        $this->discountAmount = 0;
        $this->voucherId = null;

        if (empty(trim($this->voucherCode))) return;

        $voucher = \App\Models\Voucher::whereRaw('LOWER(code) = ?', [strtolower($this->voucherCode)])
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->first();

        if (!$voucher) {
            $this->addError('voucherCode', 'Invalid or expired voucher.');
            return;
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            $this->addError('voucherCode', 'Voucher limit reached.');
            return;
        }

        if ($this->subtotal < $voucher->min_order) {
            $this->addError('voucherCode', "Minimum order is RM {$voucher->min_order}");
            return;
        }

        // Apply discount
        if ($voucher->type === 'fixed') {
            $this->discountAmount = $voucher->value;
        } elseif ($voucher->type === 'percentage') {
            $this->discountAmount = $this->subtotal * ($voucher->value / 100);
        } elseif ($voucher->type === 'free_shipping') {
            $this->discountAmount = $this->shippingCost;
        }

        // Don't discount more than total
        $maxDiscount = $this->subtotal + $this->shippingCost;
        if ($this->discountAmount > $maxDiscount) {
            $this->discountAmount = $maxDiscount;
        }

        $this->voucherId = $voucher->id;
        $this->resetErrorBag('voucherCode');
    }

    public function placeOrder()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:20',
            'payment_method' => 'required|in:billplz,stripe,cod,manual_transfer',
            'courier' => 'required',
        ]);

        // Bug-19: Refresh cart to prevent stale state
        $this->cart = \App\Models\Cart::with(['items.product', 'items.variant'])
            ->where('id', $this->cart->id)->first();
            
        if (!$this->cart || $this->cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        // Bug-M03: Recalculate subtotal
        $this->subtotal = 0;
        foreach ($this->cart->items as $item) {
            $this->subtotal += ($item->effective_price * $item->qty);
        }

        // Bug-PM-04: Validate payment method is actually enabled
        $this->validatePaymentMethodEnabled();

        // Bug-PM-05: Implement SST
        $sstEnabled = \App\Models\Setting::isEnabled('sst_enabled');
        $sstRate = $sstEnabled ? (float) \App\Models\Setting::get('sst_rate', 0) : 0;
        $taxAmount = $sstEnabled ? ($this->subtotal * $sstRate / 100) : 0;

        $grandTotal = $this->subtotal + $this->shippingCost + $taxAmount;
        
        $addressId = null;

        if (auth()->check()) {
            $user = auth()->user();
            if (empty($user->phone)) {
                $user->update(['phone' => $this->phone]);
            }

            $address = \App\Models\Address::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'address' => $this->address_line,
                    'city' => $this->city,
                    'state' => $this->state,
                    'postcode' => $this->postcode,
                ],
                ['label' => 'Address']
            );
            $addressId = $address->id;
        }

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($grandTotal, $addressId, $sstRate, $taxAmount) {
                // Bug-05: Lock for update inside transaction
                foreach ($this->cart->items as $item) {
                    $product = \App\Models\Product::lockForUpdate()->find($item->product_id);
                    if (!$product) {
                        throw new \Exception("Product '{$item->product->name}' not found.");
                    }
                    if ($product->stock !== null && $product->stock < $item->qty) {
                        throw new \Exception("Product '{$item->product->name}' is out of stock.");
                    }
                    
                    if ($item->variant_id) {
                        $variant = \App\Models\ProductVariant::lockForUpdate()->find($item->variant_id);
                        if (!$variant || ($variant->stock !== null && $variant->stock < $item->qty)) {
                            throw new \Exception("Variant '{$item->variant->value}' for '{$item->product->name}' is out of stock.");
                        }
                    }
                }

                // Generate Unique Order Number (Bug-09, Bug-C04)
                $orderNumber = null;
                $maxRetries = 3;
                for ($i = 0; $i < $maxRetries; $i++) {
                    $orderNumber = 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8));
                    if (!\App\Models\Order::where('order_number', $orderNumber)->exists()) {
                        break;
                    }
                }
                if (\App\Models\Order::where('order_number', $orderNumber)->exists()) {
                    throw new \Exception('Unable to generate unique order number. Please try again.');
                }

                // 1. Create Order
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'address_id' => $addressId,
                    'guest_name' => $this->name,
                    'guest_email' => $this->email,
                    'guest_phone' => $this->phone,
                    'guest_address' => $this->address_line,
                    'guest_city' => $this->city,
                    'guest_state' => $this->state,
                    'guest_postcode' => $this->postcode,
                    'status' => 'pending',
                    'total' => $grandTotal - $this->discountAmount, // Apply discount
                    'shipping_cost' => $this->shippingCost,
                    'courier' => $this->courier, // Bug-10: Save code instead of name
                    'tax_rate' => $sstRate,
                    'tax_amount' => $taxAmount,
                    'order_number' => $orderNumber,
                    'voucher_id' => $this->voucherId, // Bug-12: Save voucher
                ]);

                if ($this->voucherId) {
                    $voucher = \App\Models\Voucher::lockForUpdate()->find($this->voucherId);
                    if (!$voucher || !$voucher->isValid()) {
                        throw new \Exception('Voucher is no longer valid.');
                    }
                    if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
                        throw new \Exception("Voucher limit reached. Please try again without the voucher.");
                    }
                    if ($this->subtotal < ($voucher->min_order ?? 0)) {
                        throw new \Exception("Minimum order for this voucher is RM {$voucher->min_order}");
                    }
                    $voucher->increment('used_count');
                }

                // 2. Create Order Items & Decrement Stock
                foreach ($this->cart->items as $item) {
                    $price = $item->effective_price;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'bundle_id' => $item->bundle_id,
                        'qty' => $item->qty,
                        'price' => $price,
                    ]);

                    // Bug-08: Only decrement stock now if manual_transfer or cod
                    if (in_array($this->payment_method, ['manual_transfer', 'cod'])) {
                        if ($item->product->stock !== null) {
                            $item->product->decrement('stock', $item->qty);
                        }
                        if ($item->variant && $item->variant->stock !== null) {
                            $item->variant->decrement('stock', $item->qty);
                        }
                    }
                }

                // 3. Create Payment record
                $paymentType = in_array($this->payment_method, ['cod', 'manual_transfer']) ? 'manual' : 'gateway';
                Payment::create([
                    'order_id' => $order->id,
                    'type' => $paymentType,
                    'method' => $this->payment_method,
                    'amount' => $grandTotal - $this->discountAmount, // Bug-C01: Include discount
                    'status' => 'pending', // Would be 'success' after gateway callback
                ]);

                // 4. Delete Cart
                $this->cart->delete();

                return $order;
            });

            $this->dispatch('cart-updated');

            // Send Email (outside transaction)
            $recipientEmail = auth()->check() ? auth()->user()->email : $this->email;
            try {
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\OrderPlaced($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send order email: " . $e->getMessage());
            }

            // Save session for guest so they can access success page
            if (!auth()->check()) {
                session()->put('last_order_id', $order->id);
            }

            // Payment Gateway redirect
            if (in_array($this->payment_method, ['billplz', 'stripe'])) {
                // Redirect to a placeholder payment processing route for now
                return redirect()->route('payment.process', ['order' => $order->id]);
            }

            // 5. Redirect to success page for COD/Manual
            return redirect()->route('checkout.success', ['order' => $order->id]);

        } catch (\Exception $e) {
            $this->addError('cart', "Failed to place order: " . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.storefront.checkout-view')
            ->extends('layouts.storefront')
            ->section('content');
    }
}
