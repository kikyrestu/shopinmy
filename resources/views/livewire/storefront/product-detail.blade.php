<div>
    @section('title', $product->name)

    <div class="bg-gray-50 py-6 border-b border-gray-100">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-sm text-gray-500 font-medium">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">{{ __('Home') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}" class="hover:text-brand-600 transition-colors">{{ $product->category->name ?? __('Category') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900 truncate max-w-[200px] sm:max-w-md">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Main Product Section -->
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-16">
            
            <!-- Left: Image Gallery -->
            <div class="w-full lg:w-1/2 flex flex-col gap-4">
                <div class="w-full aspect-square bg-gray-50 rounded-3xl overflow-hidden border border-gray-100 relative group">
                    @if($selectedImage)
                        <img src="{{ $selectedImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <i class="ph ph-image text-6xl"></i>
                        </div>
                    @endif
                </div>

                <!-- Thumbnails -->
                <div class="flex gap-4 overflow-x-auto hide-scrollbar pb-2">
                    @if($product->primaryImage)
                    @php $primaryUrl = \Illuminate\Support\Facades\Storage::url($product->primaryImage->path); @endphp
                    <button wire:click="changeImage('{{ $primaryUrl }}')" class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all {{ $selectedImage == $primaryUrl ? 'border-brand-500 shadow-md' : 'border-transparent hover:border-gray-200 opacity-70 hover:opacity-100' }}">
                        <img src="{{ $primaryUrl }}" class="w-full h-full object-cover">
                    </button>
                    @endif

                    @foreach($product->productImages as $image)
                    @php $imgUrl = \Illuminate\Support\Facades\Storage::url($image->path); @endphp
                    <button wire:click="changeImage('{{ $imgUrl }}')" class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all {{ $selectedImage == $imgUrl ? 'border-brand-500 shadow-md' : 'border-transparent hover:border-gray-200 opacity-70 hover:opacity-100' }}">
                        <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach

                    @if(!empty($product->images))
                        @foreach($product->images as $path)
                        @php $imgUrl = \Illuminate\Support\Facades\Storage::url($path); @endphp
                        <button wire:click="changeImage('{{ $imgUrl }}')" class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all {{ $selectedImage == $imgUrl ? 'border-brand-500 shadow-md' : 'border-transparent hover:border-gray-200 opacity-70 hover:opacity-100' }}">
                            <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                        </button>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="w-full lg:w-1/2 flex flex-col">
                <div class="mb-2">
                    @if($product->brand)
                    <a href="{{ route('products.index', ['brand[]' => $product->brand_id]) }}" class="text-sm font-bold text-brand-600 hover:text-brand-700 uppercase tracking-wider">{{ $product->brand->name }}</a>
                    @endif
                </div>
                
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-4">
                    {{ $product->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 mb-6 text-sm">
                    <div class="flex items-center gap-1.5 bg-gray-50 px-3 py-1 rounded-full">
                        <i class="ph-fill ph-star text-amber-400 text-base"></i>
                        <span class="font-bold text-gray-900">{{ number_format($product->reviews_avg_rating ?? 0, 1) }}</span>
                        <span class="text-gray-500 underline cursor-pointer hover:text-brand-600">({{ $product->reviews_count }} {{ __('Reviews') }})</span>
                    </div>
                    <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                    <div class="text-gray-600 font-medium">
                        {{ $product->order_items_sum_qty ?? 0 }} <span class="text-gray-500">{{ __('terjual') }}</span>
                    </div>
                    @php
                        $displayStock = $product->variants->isNotEmpty() ? $product->variants->sum('stock') : $product->stock;
                    @endphp
                    @if($displayStock > 0)
                    <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                    <div class="text-emerald-600 font-bold flex items-center gap-1">
                        <i class="ph-fill ph-check-circle"></i> {{ __('Stok Tersedia:') }} {{ $displayStock }}
                    </div>
                    @else
                    <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                    <div class="text-red-500 font-bold flex items-center gap-1">
                        <i class="ph-fill ph-x-circle"></i> {{ __('Out of Stock') }}
                    </div>
                    @endif
                </div>

                <div class="mb-8">
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-extrabold text-gray-900">RM {{ number_format($currentPrice, 2) }}</span>
                    </div>
                </div>

                <!-- Variants -->
                @if($product->variants->isNotEmpty())
                    @php
                        $groupedVariants = $product->variants->groupBy('name');
                    @endphp

                    @foreach($groupedVariants as $name => $variants)
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-3">{{ $name }}: <span class="text-brand-600">{{ $selectedVariants[$name] ?? '' }}</span></h3>
                        <div class="flex flex-wrap gap-3">
                            @foreach($variants as $variant)
                            <button 
                                wire:click="selectVariant('{{ $name }}', '{{ $variant->value }}')"
                                class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 transition-all {{ ($selectedVariants[$name] ?? '') === $variant->value ? 'border-brand-500 bg-brand-50 text-brand-600' : 'border-gray-200 text-gray-600 hover:border-gray-300 bg-white' }}">
                                {{ $variant->value }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif

                <hr class="border-gray-100 my-8">

                <!-- Add to Cart Area -->
                <div class="flex flex-col sm:flex-row gap-4 mb-10">
                    <!-- Quantity -->
                    <div class="flex items-center bg-gray-50 rounded-2xl border border-gray-100 p-1 w-max {{ $this->maxStock !== null && $this->maxStock <= 0 ? 'opacity-50 pointer-events-none' : '' }}">
                        <button wire:click="decrementQty" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-xl transition-all disabled:opacity-50" {{ $qty <= 1 ? 'disabled' : '' }}>
                            <i class="ph ph-minus font-bold"></i>
                        </button>
                        <div class="w-12 text-center font-bold text-gray-900">{{ $qty }}</div>
                        <button wire:click="incrementQty" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-xl transition-all disabled:opacity-50" {{ ($this->maxStock !== null && $qty >= $this->maxStock) ? 'disabled' : '' }}>
                            <i class="ph ph-plus font-bold"></i>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex-1 flex gap-3">
                        <button wire:click="addToCart" class="flex-1 text-white font-bold rounded-2xl flex items-center justify-center gap-2 transition-all transform active:scale-95 disabled:opacity-50 disabled:active:scale-100 disabled:cursor-not-allowed {{ $this->maxStock !== null && $this->maxStock <= 0 ? 'bg-gray-400' : 'bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30' }}" {{ $qty < 1 || ($this->maxStock !== null && $this->maxStock <= 0) ? 'disabled' : '' }}>
                            @if($this->maxStock !== null && $this->maxStock <= 0)
                                <i class="ph-fill ph-x-circle text-xl"></i> {{ __('Out of Stock') }}
                            @else
                                <i class="ph ph-shopping-cart-simple text-xl"></i> {{ __('Add to Cart') }}
                            @endif
                        </button>
                        <button wire:click="toggleWishlist" class="w-14 h-14 border rounded-2xl flex items-center justify-center transition-all flex-shrink-0 {{ $isWishlisted ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 text-gray-400' }}">
                            <i class="{{ $isWishlisted ? 'ph-fill' : 'ph' }} ph-heart text-2xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Info blocks -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-brand-500 shadow-sm"><i class="ph ph-truck text-xl"></i></div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium">{{ __('Shipping') }}</div>
                            <div class="text-sm font-bold text-gray-900">{{ __('Free Delivery') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-brand-500 shadow-sm"><i class="ph ph-shield-check text-xl"></i></div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium">{{ __('Warranty') }}</div>
                            <div class="text-sm font-bold text-gray-900">1 {{ __('Year') }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-20" x-data="{ activeTab: 'description' }">
            <div class="flex items-center gap-8 border-b border-gray-200 mb-8 overflow-x-auto hide-scrollbar">
                <button @click="activeTab = 'description'" :class="activeTab === 'description' ? 'text-brand-600 border-brand-600' : 'text-gray-500 hover:text-gray-900 border-transparent'" class="pb-4 font-bold border-b-2 transition-colors whitespace-nowrap">{{ __('Description') }}</button>
                <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'text-brand-600 border-brand-600' : 'text-gray-500 hover:text-gray-900 border-transparent'" class="pb-4 font-bold border-b-2 transition-colors whitespace-nowrap">{{ __('Reviews') }} ({{ $product->reviews_count }})</button>
            </div>

            <!-- Description Tab -->
            <div x-show="activeTab === 'description'" class="max-w-4xl">
                <div class="prose prose-brand max-w-none text-gray-600 leading-relaxed">
                    @if($product->description)
                        {!! nl2br(e($product->description)) !!}
                    @else
                        <p class="text-gray-400 italic">{{ __('No description available.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Reviews Tab -->
            <div x-show="activeTab === 'reviews'" x-cloak class="max-w-4xl">
                
                <!-- Rating Summary -->
                <div class="flex items-center gap-6 p-6 bg-gray-50 rounded-2xl mb-8">
                    <div class="text-center">
                        <div class="text-5xl font-extrabold text-gray-900">{{ number_format($product->reviews_avg_rating ?? 0, 1) }}</div>
                        <div class="flex items-center gap-0.5 justify-center mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="ph-fill ph-star text-lg {{ $i <= round($product->reviews_avg_rating ?? 0) ? 'text-amber-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                        <div class="text-sm text-gray-500 mt-1 font-medium">{{ $product->reviews_count }} {{ __('Reviews') }}</div>
                    </div>
                </div>

                <!-- Existing Reviews -->
                @if($product->reviews->isNotEmpty())
                    <div class="space-y-6 mb-10">
                        @foreach($product->reviews as $review)
                        <div class="bg-white border border-gray-100 rounded-2xl p-6">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold text-sm">{{ substr($review->user->name, 0, 1) }}</div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $review->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ph-fill ph-star text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed">{{ $review->comment }}</p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 mb-8">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-4">
                            <i class="ph ph-chat-dots text-3xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">{{ __('No reviews yet. Be the first to review this product!') }}</p>
                    </div>
                @endif

                <!-- Write Review Form -->
                @auth
                    @if($this->hasPurchased)
                    <div class="bg-white border border-gray-100 rounded-2xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Write a Review') }}</h3>
                        <form wire:submit.prevent="submitReview">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Rating') }}</label>
                                <div class="flex items-center gap-1" x-data="{ hoverRating: 0 }">
                                    @for($i = 1; $i <= 5; $i++)
                                    <button type="button" 
                                        wire:click="$set('reviewRating', {{ $i }})" 
                                        @mouseenter="hoverRating = {{ $i }}" 
                                        @mouseleave="hoverRating = 0"
                                        class="text-3xl transition-transform hover:scale-110">
                                        <i class="ph-fill ph-star {{ $i <= $reviewRating ? 'text-amber-400' : 'text-gray-300' }}"
                                           :class="hoverRating >= {{ $i }} ? 'text-amber-400' : ''"></i>
                                    </button>
                                    @endfor
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Your Review') }}</label>
                                <textarea wire:model="reviewComment" rows="4" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all resize-none" placeholder="{{ __('Share your experience with this product...') }}"></textarea>
                                @error('reviewComment') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-sm transition-colors">{{ __('Submit Review') }}</button>
                        </form>
                    </div>
                    @else
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center mb-8">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 mx-auto mb-3">
                            <i class="ph-fill ph-lock-key text-xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium">{{ __('Anda hanya dapat memberikan ulasan setelah membeli produk ini.') }}</p>
                    </div>
                    @endif
                @else
                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    <p class="text-gray-600 font-medium">{{ __('Please log in to write a review.') }}</p>
                    <a href="{{ route('login') }}" class="mt-3 inline-block px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-colors">{{ __('Log In') }}</a>
                </div>
                @endauth
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->isNotEmpty())
        <div class="mt-24">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">{{ __('Related Products') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                @foreach($relatedProducts as $related)
                <a href="{{ route('product.show', $related->slug) }}" class="product-card group bg-white rounded-2xl p-3 border border-gray-100 flex flex-col relative h-full">
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-gray-50 mb-4">
                        <img src="{{ $related->first_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight group-hover:text-brand-600">{{ $related->name }}</h3>
                            <div class="mt-2.5 flex items-baseline gap-2">
                                <span class="text-lg font-bold text-gray-900">RM {{ number_format($related->price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <!-- Notification Toast (Livewire) -->
    <div x-data="{ show: false, message: '' }" 
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => { show = false }, 3000)"
         class="fixed bottom-24 right-5 z-50">
        <div x-show="show" x-transition.opacity.duration.300ms class="bg-gray-900 text-white px-6 py-3 rounded-xl shadow-xl font-medium flex items-center gap-3">
            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i>
            <span x-text="message"></span>
        </div>
    </div>

    <!-- Sticky Mobile CTA -->
    <div class="fixed bottom-16 left-0 w-full bg-white border-t border-gray-100 p-3 flex gap-3 z-40 md:hidden shadow-[0_-10px_20px_rgba(0,0,0,0.05)] pb-safe">
        <button wire:click="toggleWishlist" class="w-12 h-12 rounded-xl flex items-center justify-center border transition-all flex-shrink-0 {{ $isWishlisted ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-gray-200 text-gray-400 hover:bg-gray-50' }}">
            <i class="{{ $isWishlisted ? 'ph-fill' : 'ph' }} ph-heart text-xl"></i>
        </button>
        <button wire:click="addToCart" class="flex-1 text-white font-bold rounded-xl flex items-center justify-center gap-2 active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100 {{ $this->maxStock !== null && $this->maxStock <= 0 ? 'bg-gray-400' : 'bg-brand-600' }}" {{ $qty < 1 || ($this->maxStock !== null && $this->maxStock <= 0) ? 'disabled' : '' }}>
            @if($this->maxStock !== null && $this->maxStock <= 0)
                <i class="ph-fill ph-x-circle text-lg"></i> {{ __('Out of Stock') }}
            @else
                <i class="ph ph-shopping-cart-simple text-lg"></i> {{ __('Add to Cart') }} &bull; RM {{ number_format($currentPrice, 2) }}
            @endif
        </button>
    </div>
</div>
