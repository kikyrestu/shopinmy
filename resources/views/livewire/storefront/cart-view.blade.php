<div>
    @section('title', __('Shopping Cart'))

    <div class="bg-gray-50 py-6 border-b border-gray-100">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-sm text-gray-500 font-medium">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">{{ __('Home') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">{{ __('Shopping Cart') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">{{ __('Shopping Cart') }}</h1>

        @if($cart && $cart->items->count() > 0)
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left: Cart Items -->
            <div class="flex-1">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6">
                        <ul class="divide-y divide-gray-100">
                            @foreach($cart->items as $item)
                                @php
                                    // Bug-P01: Use effective_price instead of base price to match checkout
                                    $price = $item->effective_price;
                                @endphp
                                <li class="py-6 flex flex-row gap-4 sm:gap-6 relative">
                                    <div class="w-20 h-20 sm:w-28 sm:h-28 flex-shrink-0 bg-gray-50 rounded-2xl overflow-hidden">
                                        @if($item->product->primaryImage)
                                            <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <i class="ph ph-image text-3xl"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start">
                                                <h3 class="text-base font-bold text-gray-900 max-w-[80%]">
                                                    <a href="{{ route('product.show', $item->product->slug) }}" class="hover:text-brand-600 transition-colors">{{ $item->product->name }}</a>
                                                </h3>
                                                <button wire:click="removeItem({{ $item->id }})" class="text-gray-400 hover:text-red-500 transition-colors p-2 -mr-2 -mt-2 rounded-full hover:bg-red-50">
                                                    <i class="ph ph-trash text-lg"></i>
                                                </button>
                                            </div>
                                            
                                            @if($item->variant)
                                            <p class="mt-1 text-sm text-gray-500 font-medium">
                                                {{ $item->variant->name }}: <span class="text-gray-900">{{ $item->variant->value }}</span>
                                            </p>
                                            @endif
                                        </div>

                                        <div class="flex items-end justify-between mt-4">
                                            <!-- Quantity -->
                                            <div class="flex items-center bg-gray-50 rounded-xl border border-gray-100 p-1">
                                                <button wire:click="decrement({{ $item->id }})" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-lg transition-all disabled:opacity-50" {{ $item->qty <= 1 ? 'disabled' : '' }}>
                                                    <i class="ph ph-minus font-bold text-xs"></i>
                                                </button>
                                                <div class="w-10 text-center font-bold text-sm text-gray-900">{{ $item->qty }}</div>
                                                <button wire:click="increment({{ $item->id }})" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-lg transition-all">
                                                    <i class="ph ph-plus font-bold text-xs"></i>
                                                </button>
                                            </div>

                                            <!-- Price -->
                                            <div class="text-right">
                                                <div class="text-sm text-gray-500 font-medium">RM {{ number_format($price, 2) }} / {{ __('item') }}</div>
                                                <div class="text-lg font-bold text-gray-900">RM {{ number_format($price * $item->qty, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm sticky top-28">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">{{ __('Order Summary') }}</h2>
                    
                    <div class="space-y-4 text-sm mb-6">
                        <div class="flex justify-between items-center text-gray-600 font-medium">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="text-gray-900 font-bold">RM {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 font-medium pb-4 border-b border-gray-100">
                            <span>{{ __('Shipping') }}</span>
                            <span class="text-emerald-600 font-bold">{{ __('Calculated at checkout') }}</span>
                        </div>
                        <div class="flex justify-between items-end pt-2">
                            <span class="text-base font-bold text-gray-900">{{ __('Estimated Total') }}</span>
                            <span class="text-2xl font-extrabold text-brand-600">RM {{ number_format($subtotal, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="hidden lg:flex w-full py-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-2xl items-center justify-center gap-2 transition-all shadow-lg shadow-brand-500/30 transform active:scale-95 text-lg">
                        {{ __('Proceed to Checkout') }} <i class="ph-bold ph-arrow-right"></i>
                    </a>

                    <div class="mt-6 flex items-center justify-center gap-2 text-xs font-bold text-gray-400">
                        <i class="ph-fill ph-lock-key"></i> {{ __('Secure Checkout') }}
                    </div>
                </div>
            </div>
        </div>

        @if(count($recommendedProducts) > 0)
        <!-- Cross-Selling Recommendations -->
        <section class="mt-16 border-t border-gray-100 pt-12">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-8">{{ __('You may also like') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                @foreach($recommendedProducts as $product)
                <!-- Product Card -->
                <a href="{{ route('product.show', $product->slug) }}" class="product-card group bg-white rounded-2xl p-3 border border-gray-100 flex flex-col relative h-full">
                    <!-- Image Wrapper -->
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-gray-50 mb-4">
                        <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <!-- Content -->
                    <div class="flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight group-hover:text-brand-600 transition-colors">{{ $product->name }}</h3>
                            <div class="mt-2.5 flex items-baseline gap-2">
                                <span class="text-lg font-bold text-gray-900">RM {{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-500 font-medium">
                            <div class="flex items-center gap-1">
                                <i class="ph-fill ph-star text-amber-400 text-sm"></i>
                                <span class="text-gray-700 font-bold">{{ number_format($product->reviews_avg_rating ?? 0, 1) }}</span>
                                <span>| {{ $product->order_items_sum_qty ?? 0 }} {{ __('sold') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="ph ph-map-pin"></i> Malaysia
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
        @else
        <!-- Empty Cart -->
        <div class="bg-white rounded-3xl border border-gray-100 p-16 flex flex-col items-center justify-center text-center shadow-sm">
            <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-8">
                <i class="ph ph-shopping-cart-simple text-6xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ __('Your cart is empty') }}</h2>
            <p class="text-gray-500 mb-8 max-w-sm">{{ __('Looks like you have not added anything to your cart yet.') }}</p>
            <a href="{{ route('products.index') }}" class="px-8 py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-full transition-all shadow-lg shadow-brand-500/30 transform hover:-translate-y-0.5">
                {{ __('Continue Shopping') }}
            </a>
        </div>
        @endif
    </main>
    @if($cart && $cart->items->count() > 0)
    <!-- Sticky Mobile CTA for Cart -->
    <div class="fixed bottom-16 left-0 w-full bg-white border-t border-gray-100 p-3 flex gap-3 z-40 md:hidden shadow-[0_-10px_20px_rgba(0,0,0,0.05)] pb-safe">
        <div class="flex-1 flex flex-col justify-center pl-2">
            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">{{ __('Total') }}</span>
            <span class="text-base font-extrabold text-brand-600 leading-none">RM {{ number_format($subtotal, 2) }}</span>
        </div>
        <a href="{{ route('checkout.index') }}" class="flex-[1.5] bg-brand-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 active:scale-95 transition-transform text-sm">
            {{ __('Checkout') }} ({{ $cart->items->sum('qty') }}) <i class="ph-bold ph-arrow-right"></i>
        </a>
    </div>
    @endif
</div>
