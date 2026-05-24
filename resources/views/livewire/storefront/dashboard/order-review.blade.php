<div class="bg-gray-50 dark:bg-[#121212] min-h-screen pb-24">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 sticky top-0 z-30 border-b border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="px-4 py-3 flex items-center gap-3">
            <a href="{{ route('dashboard.orders') }}" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-brand-600">
                <i class="ph-bold ph-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ __('Beri Ulasan') }}</h1>
        </div>
    </div>

    <form wire:submit.prevent="submitReview" class="max-w-3xl mx-auto px-4 mt-6 flex flex-col gap-6">
        
        <!-- Courier Rating -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-brand-50 dark:bg-brand-900/30 rounded-full flex items-center justify-center text-brand-500">
                    <i class="ph-fill ph-truck text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Pelayanan Kurir</h3>
                    <p class="text-xs text-gray-500">Bagaimana pengiriman pesananmu?</p>
                </div>
            </div>
            <div class="flex items-center gap-2" x-data="{ hoverRating: 0 }">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" 
                    wire:click="setCourierRating({{ $i }})"
                    @mouseenter="hoverRating = {{ $i }}" 
                    @mouseleave="hoverRating = 0"
                    class="text-4xl transition-transform hover:scale-110">
                    <i class="ph-fill ph-star {{ $i <= $courierRating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}"
                       :class="hoverRating >= {{ $i }} ? 'text-amber-400' : ''"></i>
                </button>
                @endfor
            </div>
        </div>

        <!-- Product Ratings -->
        @foreach($order->items as $item)
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
            <div class="flex gap-4 mb-4">
                <div class="w-16 h-16 bg-gray-50 dark:bg-[#121212] rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden flex-shrink-0">
                    @if($item->product->primaryImage)
                        <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                    @else
                        <i class="ph ph-image text-gray-300 w-full h-full flex items-center justify-center text-xl"></i>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2">{{ $item->product->name }}</h4>
                    <p class="text-xs text-gray-500 mt-1">Kualitas produk ini bagaimana?</p>
                </div>
            </div>

            <!-- Stars -->
            <div class="flex items-center gap-2 mb-4" x-data="{ hoverRating: 0 }">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" 
                    wire:click="setProductRating({{ $item->product_id }}, {{ $i }})"
                    @mouseenter="hoverRating = {{ $i }}" 
                    @mouseleave="hoverRating = 0"
                    class="text-4xl transition-transform hover:scale-110">
                    <i class="ph-fill ph-star {{ $i <= $productRatings[$item->product_id]['rating'] ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}"
                       :class="hoverRating >= {{ $i }} ? 'text-amber-400' : ''"></i>
                </button>
                @endfor
            </div>

            <!-- Comment -->
            <textarea wire:model="productRatings.{{ $item->product_id }}.comment" rows="3" placeholder="Ceritakan kepuasanmu terhadap produk ini..." class="w-full bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all resize-none"></textarea>
            @error('productRatings.'.$item->product_id.'.comment') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        @endforeach

        <!-- Submit Button (Sticky Bottom) -->
        <div class="fixed left-0 w-full bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 p-4 flex items-center z-40 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] dark:shadow-none" style="bottom: env(safe-area-inset-bottom);">
            <div class="max-w-3xl mx-auto w-full">
                <button type="submit" class="w-full h-[46px] bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl flex items-center justify-center transition-colors shadow-lg shadow-brand-500/30">
                    Kirim Ulasan
                </button>
            </div>
        </div>
    </form>
</div>
