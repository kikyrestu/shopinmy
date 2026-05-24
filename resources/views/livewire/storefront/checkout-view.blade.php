<div>
    @section('title', __('Secure Checkout'))

    <div class="bg-gray-50 py-6 border-b border-gray-100">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-sm text-gray-500 font-medium">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('cart.index') }}" class="hover:text-brand-600 transition-colors">{{ __('Shopping Cart') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">{{ __('Checkout') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="max-w-[1440px] mx-auto px-0 md:px-4 sm:px-6 lg:px-8 py-4 md:py-10">
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6 md:mb-8 px-4 md:px-0">{{ __('Secure Checkout') }}</h1>

        <form wire:submit.prevent="placeOrder" class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Checkout Forms -->
            <div class="flex-1 space-y-8">
                
                <!-- Contact Info -->
                <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 shadow-sm p-4 md:p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i class="ph-fill ph-user text-brand-500"></i> {{ __('Contact Information') }}
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Full Name') }}</label>
                            <input type="text" wire:model="name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Phone Number') }}</label>
                            <input type="text" wire:model="phone" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                            @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Email Address') }}</label>
                            <input type="email" wire:model="email" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 shadow-sm p-4 md:p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="ph-fill ph-map-pin text-brand-500"></i> {{ __('Shipping Address') }}
                        </h2>
                        @if(count($userAddresses) > 0 && !$showNewAddressForm)
                            <button wire:click="$set('showNewAddressForm', true)" class="text-sm font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                                <i class="ph-bold ph-plus"></i> {{ __('New Address') }}
                            </button>
                        @endif
                    </div>
                    
                    @if(count($userAddresses) > 0 && !$showNewAddressForm)
                        <!-- Address Book Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($userAddresses as $addr)
                                <label class="block relative cursor-pointer group">
                                    <input type="radio" wire:model.live="selectedAddressId" value="{{ $addr->id }}" class="peer sr-only">
                                    <div class="h-full bg-white rounded-2xl border-2 {{ $selectedAddressId == $addr->id ? 'border-brand-500 ring-4 ring-brand-50' : 'border-gray-100 group-hover:border-gray-200' }} shadow-sm p-5 transition-all">
                                        @if($addr->is_default)
                                            <span class="absolute top-0 right-0 bg-brand-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl rounded-tr-2xl uppercase tracking-wider">{{ __('Default') }}</span>
                                        @endif
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-10 h-10 {{ $selectedAddressId == $addr->id ? 'bg-brand-500 text-white' : 'bg-brand-50 text-brand-600' }} rounded-full flex items-center justify-center transition-colors">
                                                <i class="ph-fill {{ strtolower($addr->label) == 'office' ? 'ph-buildings' : 'ph-house' }} text-xl"></i>
                                            </div>
                                            <div class="font-bold text-gray-900">{{ $addr->label ?? __('Address') }}</div>
                                        </div>
                                        <div class="text-sm text-gray-600 leading-relaxed">
                                            {{ $addr->address }}<br>
                                            {{ $addr->city }}, {{ $addr->postcode }}<br>
                                            {{ $addr->state }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <!-- Manual Input Form -->
                        @if(count($userAddresses) > 0)
                            <div class="mb-4 flex justify-end">
                                <button wire:click="$set('showNewAddressForm', false)" class="text-sm font-semibold text-gray-500 hover:text-gray-700 flex items-center gap-1">
                                    <i class="ph-bold ph-arrow-left"></i> {{ __('Back to Address Book') }}
                                </button>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Street Address') }}</label>
                                <input type="text" wire:model="address_line" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                @error('address_line') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('City') }}</label>
                                <input type="text" wire:model="city" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                @error('city') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('State') }}</label>
                                <input type="text" wire:model="state" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                @error('state') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Postcode') }}</label>
                                <input type="text" wire:model.live.debounce.500ms="postcode" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                @error('postcode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Shipping Courier -->
                <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 shadow-sm p-4 md:p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i class="ph-fill ph-truck text-brand-500"></i> {{ __('Shipping Courier') }}
                    </h2>
                    
                    @if($isCalculatingShipping)
                        <div class="flex items-center gap-3 text-gray-500 py-4">
                            <i class="ph-bold ph-spinner animate-spin text-xl text-brand-500"></i>
                            <span>{{ __('Calculating shipping rates...') }}</span>
                        </div>
                    @elseif($shippingError)
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm">
                            {{ $shippingError }}
                        </div>
                    @elseif(empty($availableCouriers))
                        <div class="text-gray-500 text-sm">
                            {{ __('Please enter your postcode to see available shipping options.') }}
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($availableCouriers as $code => $rate)
                                <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all {{ $courier === $code ? 'border-brand-500 bg-brand-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="courier" wire:model.live="courier" value="{{ $code }}" class="w-5 h-5 text-brand-600 focus:ring-brand-500">
                                        <span class="font-bold text-gray-900">{{ $rate['name'] }}</span>
                                    </div>
                                    <div class="font-bold text-brand-600">RM {{ number_format($rate['price'], 2) }}</div>
                                </label>
                            @endforeach
                        </div>
                        @error('courier') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                    @endif
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 shadow-sm p-4 md:p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i class="ph-fill ph-credit-card text-brand-500"></i> {{ __('Payment Method') }}
                    </h2>
                    
                    <div class="space-y-3">
                        <!-- Billplz (FPX) -->
                        @if(\App\Models\Setting::isEnabled('billplz_enabled') && \App\Models\Setting::get('billplz_api_key'))
                        <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all {{ $payment_method === 'billplz' ? 'border-brand-500 bg-brand-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" wire:model="payment_method" value="billplz" class="w-5 h-5 text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-gray-900">FPX Online Banking (Billplz)</span>
                            </div>
                            <div class="font-bold text-blue-800 italic">FPX</div>
                        </label>
                        @endif
                        
                        <!-- Stripe -->
                        @if(\App\Models\Setting::isEnabled('stripe_enabled') && \App\Models\Setting::get('stripe_publishable_key'))
                        <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all {{ $payment_method === 'stripe' ? 'border-brand-500 bg-brand-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" wire:model="payment_method" value="stripe" class="w-5 h-5 text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-gray-900">Credit / Debit Card (Stripe)</span>
                            </div>
                            <div class="flex gap-1 text-gray-400">
                                <i class="ph-fill ph-stripe-logo text-2xl"></i>
                            </div>
                        </label>
                        @endif

                        <!-- Manual Transfer -->
                        @if(\App\Models\Setting::isEnabled('manual_transfer_enabled'))
                        <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all {{ $payment_method === 'manual_transfer' ? 'border-brand-500 bg-brand-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" wire:model="payment_method" value="manual_transfer" class="w-5 h-5 text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-gray-900">Manual Bank Transfer</span>
                            </div>
                        </label>
                        @endif
                        
                        <!-- COD -->
                        @if(\App\Models\Setting::isEnabled('cod_enabled'))
                        <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all {{ $payment_method === 'cod' ? 'border-brand-500 bg-brand-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" wire:model="payment_method" value="cod" class="w-5 h-5 text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-gray-900">Cash on Delivery</span>
                            </div>
                            <i class="ph-fill ph-money text-2xl text-emerald-500"></i>
                        </label>
                        @endif
                    </div>
                    @error('payment_method') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <!-- Right: Order Summary -->
            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 p-4 md:p-6 shadow-sm sticky top-28">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">{{ __('Order Summary') }}</h2>
                    
                    <ul class="space-y-4 mb-6">
                        @foreach($cart->items as $item)
                        <li class="flex gap-4">
                            <div class="w-16 h-16 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100 relative">
                                @if($item->product->primaryImage)
                                    <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                                @else
                                    <i class="ph ph-image text-gray-300 w-full h-full flex items-center justify-center"></i>
                                @endif
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-gray-900 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $item->qty }}</span>
                            </div>
                            <div class="flex-1 text-sm">
                                <div class="font-bold text-gray-900 line-clamp-2">{{ $item->product->name }}</div>
                                @if($item->variant)
                                    <div class="text-gray-500 mt-0.5">{{ $item->variant->value }}</div>
                                @endif
                            </div>
                            @php
                                $price = $item->effective_price;
                            @endphp
                            <div class="font-bold text-gray-900">RM {{ number_format($price * $item->qty, 2) }}</div>
                        </li>
                        @endforeach
                    </ul>

                    <hr class="border-gray-100 mb-6 mt-6">

                    <!-- Bug-U02: Voucher Section -->
                    <div class="mb-6">
                        <div class="flex gap-2">
                            <input type="text" wire:model.defer="voucherCode" placeholder="{{ __('Enter Voucher Code') }}" class="flex-1 w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all uppercase">
                            <button type="button" wire:click="applyVoucher" class="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition-all" wire:loading.attr="disabled" wire:target="applyVoucher">
                                <span wire:loading.remove wire:target="applyVoucher">{{ __('Apply') }}</span>
                                <i class="ph-bold ph-spinner animate-spin" wire:loading wire:target="applyVoucher"></i>
                            </button>
                        </div>
                        @error('voucherCode')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                        @if($discountAmount > 0)
                            <p class="text-green-600 text-sm mt-2 font-medium"><i class="ph-bold ph-check-circle inline-block align-middle"></i> {{ __('Voucher applied successfully!') }}</p>
                        @endif
                    </div>

                    <div class="space-y-3 text-sm mb-6">
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="text-gray-900 font-bold">RM {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>{{ __('Shipping') }} {{ $courier && isset($availableCouriers[$courier]) ? '(' . $availableCouriers[$courier]['name'] . ')' : '' }}</span>
                            <span class="text-gray-900 font-bold">RM {{ number_format($shippingCost, 2) }}</span>
                        </div>
                        
                        @php
                            $sstEnabled = \App\Models\Setting::isEnabled('sst_enabled');
                            $sstRate = $sstEnabled ? (float) \App\Models\Setting::get('sst_rate', 0) : 0;
                            $taxAmount = $sstEnabled ? ($subtotal * $sstRate / 100) : 0;
                        @endphp
                        
                        @if($sstEnabled && $taxAmount > 0)
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>{{ \App\Models\Setting::get('sst_label', 'SST (' . $sstRate . '%)') }}</span>
                            <span class="text-gray-900 font-bold">RM {{ number_format($taxAmount, 2) }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-end pt-4 border-t border-gray-100 mt-2">
                            <span class="text-base font-bold text-gray-900">{{ __('Total') }}</span>
                            <span class="text-2xl font-extrabold text-brand-600">RM {{ number_format($subtotal + $shippingCost + $taxAmount - $discountAmount, 2) }}</span>
                        </div>
                    </div>

                    @error('cart')
                        <div class="mb-4 bg-red-50 text-red-600 p-4 rounded-xl text-sm font-medium border border-red-100 flex gap-2">
                            <i class="ph-fill ph-warning-circle text-lg flex-shrink-0"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <button type="submit" class="hidden lg:flex w-full py-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-2xl items-center justify-center gap-2 transition-all shadow-lg shadow-brand-500/30 transform active:scale-95 text-lg" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                        <span wire:loading.remove>{{ __('Place Order') }}</span>
                        <span wire:loading><i class="ph-bold ph-spinner animate-spin"></i> {{ __('Processing...') }}</span>
                        <i class="ph-bold ph-arrow-right" wire:loading.remove></i>
                    </button>
                    
                    <p class="text-center text-xs text-gray-400 mt-4 px-2">
                        {{ __('By placing your order, you agree to our Terms & Conditions and Privacy Policy.') }}
                    </p>
                </div>
            </div>
        </form>
    </main>

    <!-- Sticky Mobile CTA for Checkout -->
    <div class="fixed bottom-14 left-0 w-full bg-white border-t border-gray-100 p-3 flex gap-3 z-40 lg:hidden shadow-[0_-10px_20px_rgba(0,0,0,0.05)] pb-safe">
        <div class="flex-1 flex flex-col justify-center pl-2">
            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">{{ __('Total') }}</span>
            <span class="text-base font-extrabold text-brand-600 leading-none">RM {{ number_format($subtotal + $shippingCost + ((\App\Models\Setting::isEnabled('sst_enabled') ? (float) \App\Models\Setting::get('sst_rate', 0) : 0) * $subtotal / 100) - $discountAmount, 2) }}</span>
        </div>
        <button wire:click="placeOrder" class="flex-[1.5] bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 active:scale-95 transition-transform text-sm disabled:opacity-50" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="placeOrder">{{ __('Place Order') }}</span>
            <span wire:loading wire:target="placeOrder"><i class="ph-bold ph-spinner animate-spin"></i></span>
            <i class="ph-bold ph-arrow-right" wire:loading.remove wire:target="placeOrder"></i>
        </button>
    </div>
</div>
