<div>
    <h1 class="text-2xl font-extrabold text-gray-900 mb-6">{{ __('My Wishlist') }}</h1>

    @if($wishlists->isEmpty())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-6">
                <i class="ph ph-heart text-5xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('Your wishlist is empty') }}</h3>
            <p class="text-gray-500 mb-6 max-w-sm mx-auto">{{ __('Save items you love to your wishlist and revisit them anytime.') }}</p>
            <a href="{{ route('products.index') }}" class="inline-block px-8 py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-full transition-all shadow-lg shadow-brand-500/30 transform hover:-translate-y-0.5">{{ __('Browse Products') }}</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($wishlists as $wishlist)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                <a href="{{ route('product.show', $wishlist->product->slug) }}" class="block">
                    <div class="w-full aspect-square bg-gray-50 overflow-hidden relative">
                        @if($wishlist->product->primaryImage)
                            <img src="{{ $wishlist->product->first_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <i class="ph ph-image text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <a href="{{ route('product.show', $wishlist->product->slug) }}" class="text-sm font-bold text-gray-900 line-clamp-2 hover:text-brand-600 transition-colors">{{ $wishlist->product->name }}</a>
                    <div class="text-lg font-extrabold text-gray-900 mt-2">RM {{ number_format($wishlist->product->price, 2) }}</div>
                    <div class="flex gap-2 mt-4">
                        <button wire:click="addToCart({{ $wishlist->product_id }})" class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5">
                            <i class="ph ph-shopping-cart-simple"></i> {{ __('Add to Cart') }}
                        </button>
                        <button wire:click="removeFromWishlist({{ $wishlist->id }})" class="w-10 h-10 flex items-center justify-center border border-gray-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 text-gray-400 rounded-xl transition-colors flex-shrink-0">
                            <i class="ph ph-trash text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <!-- Notification Toast -->
    <div x-data="{ show: false, message: '' }" 
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => { show = false }, 3000)"
         class="fixed bottom-5 right-5 z-50">
        <div x-show="show" x-transition.opacity.duration.300ms class="bg-gray-900 text-white px-6 py-3 rounded-xl shadow-xl font-medium flex items-center gap-3">
            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i>
            <span x-text="message"></span>
        </div>
    </div>
</div>
