<div x-data="{ showPromoModal: false }">
    @section('title', __('Shopping Cart'))

    <!-- MOBILE NATIVE HEADER (Tokopedia Style) -->
    <div class="md:hidden sticky top-0 z-50 bg-white dark:bg-[#121212] border-b border-gray-100 dark:border-gray-800 pb-2 pt-2">
        <!-- Top row: Back, Title, Icons -->
        <div class="flex items-center justify-between px-4 h-12">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}" class="text-gray-900 dark:text-gray-100">
                    <i class="ph ph-arrow-left text-2xl"></i>
                </a>
                <h1 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">{{ __('Keranjang') }}</h1>
            </div>
            <div class="flex items-center gap-4 text-gray-900 dark:text-gray-100">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::get('whatsapp_number', '60123456789')) }}" target="_blank">
                    <i class="ph ph-chat-teardrop text-2xl"></i>
                </a>
                <a href="{{ route('dashboard.wishlist') }}">
                    <i class="ph ph-heart text-2xl"></i>
                </a>
                <a href="{{ route('dashboard') }}">
                    <i class="ph ph-list text-2xl"></i>
                </a>
            </div>
        </div>
        <!-- Sub row: "1 produk terpilih" and "Hapus" -->
        <div class="flex items-center justify-between px-4 mt-1">
            <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                {{ count($selectedItems) }} {{ __('produk terpilih') }}
            </div>
            <button wire:click="removeSelectedItems" class="text-brand-500 font-bold text-sm hover:text-brand-600 transition-colors">{{ __('Hapus') }}</button>
        </div>
    </div>

    <!-- DESKTOP HEADER (Hidden on mobile) -->
    <div class="hidden md:block bg-gray-50 dark:bg-[#121212] py-6 border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-sm text-gray-500 dark:text-gray-400 font-medium">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">{{ __('Home') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900 dark:text-gray-100">{{ __('Shopping Cart') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="max-w-[1440px] mx-auto md:px-4 sm:px-6 lg:px-8 py-0 md:py-10 pb-40 md:pb-10 bg-gray-50 dark:bg-[#121212] md:bg-transparent">
        <h1 class="hidden md:block text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-6 md:mb-8">{{ __('Shopping Cart') }}</h1>

        @if($cart && $cart->items->count() > 0)
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Cart Items -->
            <div class="flex-1">
                <!-- Group By Store -->
                <div class="bg-white dark:bg-[#18181B] md:rounded-3xl border-y md:border border-gray-100 dark:border-gray-800 md:shadow-sm overflow-hidden mb-2">
                    <!-- Store Header -->
                    <div class="p-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <!-- Green Checkbox -->
                            <input type="checkbox" wire:model.live="selectAll" class="w-5 h-5 rounded text-brand-500 focus:ring-brand-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800 cursor-pointer flex-shrink-0">
                            <a href="{{ route('home') }}" class="font-bold text-gray-900 dark:text-gray-100 hover:text-brand-600 transition-colors flex items-center gap-1">
                                {{ \App\Models\Setting::get('site_name', 'Toko Utama') }}
                                <i class="ph-bold ph-caret-right text-xs text-gray-400"></i>
                            </a>
                        </div>
                        @if(\App\Models\Setting::isEnabled('free_shipping_enabled', true))
                        <span class="text-[10px] font-extrabold text-brand-500 italic tracking-wider">{{ __('GRATIS ONGKIR') }}</span>
                        @endif
                    </div>

                    <!-- Items -->
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($cart->items as $item)
                            @php
                                $price = $item->effective_price;
                                $basePrice = $item->product->price;
                                $hasDiscount = $price < $basePrice;
                                $discountPercent = $hasDiscount ? round((($basePrice - $price) / $basePrice) * 100) : 0;
                            @endphp
                            <li class="p-4 flex gap-3 relative">
                                <!-- Checkbox -->
                                <div class="pt-6 flex-shrink-0">
                                    <input type="checkbox" wire:model.live="selectedItems" value="{{ $item->id }}" class="w-5 h-5 rounded text-brand-500 focus:ring-brand-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800 cursor-pointer">
                                </div>
                                
                                <!-- Image -->
                                <a href="{{ route('product.show', $item->product->slug) }}" class="block w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 bg-gray-50 dark:bg-[#121212] rounded-xl overflow-hidden mt-1 border border-gray-100 dark:border-gray-800">
                                    @if($item->product->primaryImage)
                                        <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
                                            <i class="ph ph-image text-3xl"></i>
                                        </div>
                                    @endif
                                </a>

                                <!-- Content -->
                                <div class="flex-1 flex flex-col justify-start">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 leading-snug">
                                        <a href="{{ route('product.show', $item->product->slug) }}">{{ $item->product->name }}</a>
                                    </h3>
                                    
                                    @if($item->variant)
                                    <p class="mt-0.5 text-xs text-gray-500 font-medium">{{ $item->variant->name }}: {{ $item->variant->value }}</p>
                                    @endif

                                    @if($item->product->reviews_avg_rating >= 4.5)
                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-amber-500 font-medium">
                                        <i class="ph-fill ph-thumbs-up"></i> 100% pembeli merasa puas!
                                    </div>
                                    @elseif($item->product->reviews_avg_rating > 0)
                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-amber-500 font-medium">
                                        <i class="ph-fill ph-star"></i> {{ number_format($item->product->reviews_avg_rating, 1) }} rating rata-rata
                                    </div>
                                    @endif

                                    <div class="mt-1">
                                        <div class="text-sm font-extrabold text-gray-900 dark:text-gray-100">
                                            @if($hasDiscount)
                                                <span class="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-[9px] px-1 rounded mr-1">Hemat</span>
                                            @endif
                                            RM{{ number_format($price, 2) }}
                                        </div>
                                        @if($hasDiscount)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 line-through">RM{{ number_format($basePrice, 2) }}</span>
                                            <span class="text-[10px] text-rose-500 font-bold">{{ $discountPercent }}%</span>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-end gap-3 mt-2">
                                        <!-- Trash Icon -->
                                        <button wire:click="removeItem({{ $item->id }})" class="text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                                            <i class="ph ph-trash text-xl"></i>
                                        </button>
                                        
                                        <!-- Quantity -->
                                        <div class="flex items-center bg-white dark:bg-[#1A1A1A] rounded-full border border-gray-200 dark:border-gray-700 h-8 px-1">
                                            <button wire:click="decrement({{ $item->id }})" class="w-6 h-full flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-brand-600 disabled:opacity-50" {{ $item->qty <= 1 ? 'disabled' : '' }}>
                                                <i class="ph ph-minus text-xs"></i>
                                            </button>
                                            <div class="w-6 text-center font-bold text-sm text-gray-900 dark:text-gray-100">{{ $item->qty }}</div>
                                            <button wire:click="increment({{ $item->id }})" class="w-6 h-full flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-brand-600">
                                                <i class="ph ph-plus text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @if(\App\Models\Setting::isEnabled('free_shipping_enabled', true))
                            <li class="px-4 py-2 bg-emerald-50/50 dark:bg-emerald-900/10 flex items-center gap-2 border-t border-dashed border-gray-100 dark:border-gray-800">
                                <i class="ph-fill ph-truck text-emerald-600 text-lg"></i>
                                <span class="text-[11px] font-medium text-gray-900 dark:text-gray-300">Kamu dapat s.d. <span class="font-bold">RM{{ number_format(\App\Models\Setting::get('free_shipping_discount_amount', 10), 2) }}</span> Gratis Ongkir!</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Right: Order Summary (Desktop) -->
            <div class="hidden lg:block w-96 flex-shrink-0">
                <div class="bg-white dark:bg-[#18181B] rounded-3xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm sticky top-28">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">{{ __('Order Summary') }}</h2>
                    
                    <div class="space-y-4 text-sm mb-6">
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 font-medium">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="text-gray-900 dark:text-gray-100 font-bold">RM{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 font-medium pb-4 border-b border-gray-100 dark:border-gray-800">
                            <span>{{ __('Shipping') }}</span>
                            <span class="text-emerald-600 font-bold">{{ __('Calculated at checkout') }}</span>
                        </div>
                        <div class="flex justify-between items-end pt-2">
                            <span class="text-base font-bold text-gray-900 dark:text-gray-100">{{ __('Estimated Total') }}</span>
                            <span class="text-2xl font-extrabold text-brand-600">RM{{ number_format($subtotal, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-brand-500/30 transform active:scale-95 text-lg">
                        {{ __('Proceed to Checkout') }} <i class="ph-bold ph-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cross-Selling Recommendations -->
        @if(count($recommendedProducts) > 0)
        <section class="mt-4 md:mt-16 bg-white dark:bg-[#18181B] md:bg-transparent pt-4 md:pt-12 md:border-t md:border-gray-100 md:dark:border-gray-800 px-4 md:px-0 pb-8">
            <h2 class="text-[15px] md:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-4">{{ __('Kamu sempat lihat-lihat ini') }}</h2>
            
            <!-- Horizontal scroll on mobile -->
            <div class="flex md:grid md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6 overflow-x-auto hide-scrollbar pb-4 -mx-4 px-4 md:mx-0 md:px-0">
                @foreach($recommendedProducts as $product)
                @php
                    $hasDiscount = $product->price < $product->base_price;
                    $discountPercent = $hasDiscount ? round((($product->base_price - $product->price) / $product->base_price) * 100) : 0;
                @endphp
                <!-- Product Card Tokopedia Style -->
                <a href="{{ route('product.show', $product->slug) }}" class="flex-shrink-0 w-36 md:w-auto bg-white dark:bg-[#121212] rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col relative overflow-hidden group shadow-sm">
                    <!-- Image Wrapper -->
                    <div class="relative w-full aspect-square bg-gray-50 dark:bg-[#1A1A1A]">
                        <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @if($hasDiscount)
                        <div class="absolute top-0 right-0 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-bl-lg">
                            {{ $discountPercent }}%
                        </div>
                        @endif
                        @if(\App\Models\Setting::isEnabled('free_shipping_enabled', true))
                        <div class="absolute bottom-0 left-0 bg-emerald-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-tr-lg flex items-center gap-0.5">
                            <i class="ph-bold ph-truck"></i> GRATIS ONGKIR
                        </div>
                        @endif
                    </div>
                    <!-- Content -->
                    <div class="p-2 flex flex-col flex-1 justify-between">
                        <div>
                            @if(\App\Models\Setting::isEnabled('enable_power_badge', true))
                            <div class="flex items-center gap-1 mb-1">
                                <i class="ph-fill ph-check-circle text-emerald-500 text-[10px]"></i>
                                <span class="text-[9px] text-emerald-600 font-bold">Power Badge</span>
                            </div>
                            @endif
                            <h3 class="text-xs font-medium text-gray-800 dark:text-gray-200 line-clamp-2 leading-snug">{{ $product->name }}</h3>
                            <div class="mt-1 font-extrabold text-brand-600 dark:text-rose-500 text-sm">
                                RM{{ number_format($product->price, 2) }}
                            </div>
                        </div>
                        <div class="mt-2 text-[10px] text-gray-500 dark:text-gray-500 flex items-center gap-1">
                            <i class="ph-fill ph-star text-amber-400"></i>
                            <span class="text-gray-700 dark:text-gray-300 font-bold">{{ number_format($product->reviews_avg_rating ?? 0, 1) }}</span>
                            <span>| {{ $product->order_items_sum_qty ?? 0 }} terjual</span>
                        </div>
                        
                        <div class="mt-2 md:hidden z-10 relative">
                            <button wire:click.prevent="addToCart({{ $product->id }})" class="w-full py-1.5 border border-brand-500 text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 font-bold text-center text-xs rounded-lg transition-colors flex justify-center items-center h-8" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="addToCart({{ $product->id }})">+ Keranjang</span>
                                <span wire:loading wire:target="addToCart({{ $product->id }})"><i class="ph-bold ph-spinner animate-spin"></i></span>
                            </button>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
        
        @else
        <!-- Empty Cart -->
        <div class="bg-white dark:bg-[#18181B] md:rounded-3xl border-b md:border border-gray-100 dark:border-gray-800 p-16 flex flex-col items-center justify-center text-center">
            <div class="w-32 h-32 bg-gray-50 dark:bg-[#121212] rounded-full flex items-center justify-center text-gray-300 dark:text-gray-600 mb-8">
                <i class="ph ph-shopping-cart-simple text-6xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Your cart is empty') }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm">{{ __('Looks like you have not added anything to your cart yet.') }}</p>
            <a href="{{ route('products.index') }}" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-full transition-all shadow-lg shadow-brand-500/30">
                {{ __('Continue Shopping') }}
            </a>
        </div>
        @endif
    </main>

    @if($cart && $cart->items->count() > 0)
    <!-- Sticky Mobile CTA for Cart (Tokopedia Style) -->
    <div class="fixed left-0 w-full bg-white dark:bg-[#1A1A1A] md:hidden z-40 shadow-[0_-4px_10px_rgba(0,0,0,0.1)]" style="bottom: calc(3.5rem + env(safe-area-inset-bottom));">
        <!-- Promo Bar -->
        <button type="button" @click="showPromoModal = true" class="w-full border-t border-b border-gray-100 dark:border-gray-800 px-4 py-2 flex items-center justify-between active:bg-gray-50 dark:active:bg-gray-800/50 transition-colors">
            <div class="flex items-center gap-2">
                @if($activeVoucher)
                <i class="ph-fill ph-ticket text-brand-500 text-xl"></i>
                <div class="flex gap-1">
                    <span class="bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 text-[10px] font-bold px-1.5 py-0.5 rounded border border-rose-200 dark:border-rose-800">{{ $activeVoucher->code }}</span>
                    <span class="bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] font-bold px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">Tersedia</span>
                </div>
                @else
                <i class="ph-fill ph-ticket text-gray-400 text-xl"></i>
                <div class="text-xs font-bold text-gray-600 dark:text-gray-400">Cek promo biar makin hemat!</div>
                @endif
            </div>
            <i class="ph-bold ph-caret-right text-gray-400 dark:text-gray-500"></i>
        </button>
        <!-- Checkout Bar -->
        <div class="px-4 py-2 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 flex-shrink-0">
                <input type="checkbox" wire:model.live="selectAll" class="w-5 h-5 rounded text-brand-500 focus:ring-brand-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800 cursor-pointer">
                <span class="text-sm text-gray-900 dark:text-gray-100 font-medium">Semua</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-[15px] font-extrabold text-gray-900 dark:text-gray-100 leading-none">RM{{ number_format($subtotal, 2) }} <i class="ph-fill ph-ticket text-rose-500 text-[10px]"></i></div>
                    <div class="text-[9px] text-gray-500 dark:text-gray-400 flex items-center gap-1 justify-end mt-1">
                        Total Diskon <span class="text-rose-500 font-bold">RM{{ number_format($totalDiscount, 2) }}</span> <i class="ph-bold ph-caret-down text-gray-400"></i>
                    </div>
                </div>
                <button wire:click="proceedToCheckout" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-sm active:scale-95 transition-transform flex-shrink-0">
                    {{ __('Beli') }} ({{ count($selectedItems) }})
                </button>
            </div>
        </div>
    </div>

    <!-- Voucher Modal (Bottom Sheet) -->
    <div x-show="showPromoModal" class="fixed inset-0 z-[100] flex items-end justify-center" x-cloak>
            <!-- Backdrop -->
            <div x-show="showPromoModal" 
                 x-transition:enter="transition-opacity ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm" 
                 @click="showPromoModal = false"></div>
            
            <!-- Modal Content -->
            <div x-show="showPromoModal" 
                 x-transition:enter="transition-transform ease-out duration-300" 
                 x-transition:enter-start="translate-y-full" 
                 x-transition:enter-end="translate-y-0" 
                 x-transition:leave="transition-transform ease-in duration-200" 
                 x-transition:leave-start="translate-y-0" 
                 x-transition:leave-end="translate-y-full"
                 class="bg-gray-50 dark:bg-[#121212] w-full max-w-md rounded-t-2xl p-4 relative z-10 h-[70vh] flex flex-col shadow-2xl">
                
                <!-- Pull Handle -->
                <div class="flex justify-center mb-4">
                    <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-extrabold text-lg text-gray-900 dark:text-gray-100">Pilih Promo</h3>
                    <button @click="showPromoModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>
                
                <!-- List Voucher Public -->
                <div class="space-y-3 max-h-[60vh] overflow-y-auto hide-scrollbar pb-20">
                    @forelse($publicVouchers as $voucher)
                        @php
                            $eligibilityError = $voucher->getEligibilityError($subtotal);
                            $isEligible = $eligibilityError === null;
                        @endphp
                        <div class="border {{ $isEligible ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900' : 'border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 opacity-70' }} rounded-xl p-4 flex items-center justify-between gap-4 transition-all">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="font-extrabold text-sm {{ $isEligible ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">{{ $voucher->description ?? 'Promo Spesial (' . $voucher->code . ')' }}</div>
                                    <span class="{{ $isEligible ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 border-brand-200 dark:border-brand-800' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-600' }} text-[10px] font-bold px-1.5 py-0.5 rounded border">{{ $voucher->code }}</span>
                                </div>
                                <div class="text-[11px] font-medium {{ $isEligible ? 'text-gray-500 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500' }} flex flex-col gap-0.5">
                                    @if($voucher->min_order > 0)
                                        <span>Min. Belanja RM{{ number_format($voucher->min_order, 2) }}</span>
                                    @endif
                                    @if($voucher->expires_at)
                                        <span>Berlaku s.d. {{ $voucher->expires_at->format('d M Y') }}</span>
                                    @endif
                                </div>
                                @if(!$isEligible)
                                    <div class="mt-2 text-[10px] font-bold text-rose-500 flex items-center gap-1">
                                        <i class="ph-fill ph-warning-circle"></i> {{ $eligibilityError }}
                                    </div>
                                @endif
                            </div>
                            
                            @if($isEligible)
                                <button wire:click="selectVoucher({{ $voucher->id }})" @click="showPromoModal = false" class="px-4 py-1.5 {{ $selectedVoucherId == $voucher->id ? 'bg-brand-100 text-brand-700' : 'bg-brand-500 hover:bg-brand-600 text-white shadow-md' }} font-bold rounded-lg text-xs transition-colors flex-shrink-0">
                                    {{ $selectedVoucherId == $voucher->id ? 'Terpasang' : 'Pakai' }}
                                </button>
                            @else
                                <button disabled class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-bold rounded-lg text-xs flex-shrink-0 cursor-not-allowed">
                                    Pakai
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-10 flex flex-col items-center">
                            <i class="ph-fill ph-ticket text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-gray-500 font-medium">Belum ada promo yang tersedia.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
