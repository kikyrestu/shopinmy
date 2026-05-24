<div x-data="{ showMobileFilters: false }">
    <!-- Header -->
    <div class="mb-8 relative">
        <div wire:loading class="absolute right-0 top-0 text-brand-500 flex items-center gap-2 font-medium text-sm">
            <i class="ph ph-spinner animate-spin text-xl"></i> {{ __('Loading...') }}
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
            @if($search)
                {{ __('Search Results for') }} <span class="text-brand-600">"{{ $search }}"</span>
            @elseif($category)
                {{ __('Category') }}: <span class="text-brand-600">{{ $categories->where('slug', $category)->first()->name ?? $category }}</span>
            @else
                {{ __('All Products') }}
            @endif
        </h1>
        <p class="text-gray-500 dark:text-gray-500 mt-2 text-sm">{{ __('Showing') }} {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} {{ __('of') }} {{ $products->total() }} {{ __('products') }}</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- SIDEBAR FILTERS -->
        <aside class="w-full lg:w-64 flex-shrink-0 z-50 lg:z-auto" :class="showMobileFilters ? 'fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-end' : 'hidden lg:block'">
            <div class="bg-white dark:bg-gray-900 lg:rounded-2xl lg:border border-gray-100 dark:border-gray-800 p-5 lg:sticky top-28 shadow-sm h-full lg:h-auto overflow-y-auto w-4/5 lg:w-full" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2"><i class="ph-bold ph-faders"></i> {{ __('Filter') }}</h2>
                    <div class="flex items-center gap-3">
                        @if($search || $category || !empty($brand) || $min_price || $max_price)
                        <button wire:click="clearFilters" class="text-xs font-semibold text-red-500 hover:text-red-600">{{ __('Clear Filters') }}</button>
                        @endif
                        <button @click="showMobileFilters = false" class="lg:hidden text-gray-400 dark:text-gray-600 hover:text-gray-900 dark:text-gray-100"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                </div>

                <div>
                    <!-- Category Filter -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Categories') }}</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto hide-scrollbar">
                            @foreach($categories as $cat)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="category" wire:model.live="category" value="{{ $cat->slug }}" 
                                    class="w-4 h-4 text-brand-500 border-gray-300 focus:ring-brand-500">
                                <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-brand-600 transition-colors {{ $category === $cat->slug ? 'font-semibold text-brand-600' : '' }}">{{ $cat->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Brands') }}</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto hide-scrollbar">
                            @foreach($brands as $b)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.live="brand" value="{{ $b->id }}"
                                    class="w-4 h-4 text-brand-500 border-gray-300 rounded focus:ring-brand-500">
                                <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-brand-600 transition-colors">{{ $b->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Filter -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Price Range') }}</h3>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-600 text-xs font-semibold">RM</span>
                                <input type="number" wire:model.live.debounce.500ms="min_price" placeholder="{{ __('Min') }}" class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-brand-500 focus:border-brand-500 outline-none transition-all">
                            </div>
                            <span class="text-gray-400 dark:text-gray-600">-</span>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-600 text-xs font-semibold">RM</span>
                                <input type="number" wire:model.live.debounce.500ms="max_price" placeholder="{{ __('Max') }}" class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-brand-500 focus:border-brand-500 outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- PRODUCT GRID AREA -->
        <div class="flex-1 relative">
            <!-- Loading Overlay for Grid -->
            <div wire:loading.flex class="absolute inset-0 bg-white dark:bg-gray-900/50 backdrop-blur-sm z-10 items-center justify-center rounded-2xl hidden">
                <div class="bg-white dark:bg-gray-900 px-6 py-3 rounded-full shadow-lg flex items-center gap-3 font-semibold text-brand-600">
                    <i class="ph ph-spinner animate-spin text-2xl"></i> {{ __('Loading...') }}
                </div>
            </div>

            <!-- Toolbar (Sorting) -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-4 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div class="w-full sm:w-auto flex items-center justify-between gap-4">
                    <div class="text-sm text-gray-500 dark:text-gray-500 font-medium">
                        {{ __('Sort By') }}:
                    </div>
                    <button @click="showMobileFilters = true" class="lg:hidden px-4 py-2 text-sm font-semibold rounded-full border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="ph-bold ph-faders"></i> {{ __('Filter') }}
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @php
                        $sorts = [
                            'latest' => __('Latest'),
                            'popular' => __('Top Sales'),
                            'price_asc' => __('Lowest Price'),
                            'price_desc' => __('Highest Price'),
                            'rating' => __('Top Rated'),
                        ];
                    @endphp

                    @foreach($sorts as $key => $label)
                    <button wire:click="setSort('{{ $key }}')" 
                       class="px-4 py-2 text-sm font-semibold rounded-full transition-all border {{ $sort === $key ? 'bg-brand-50 border-brand-200 text-brand-600' : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212]' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Products -->
            @if($products->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 md:gap-6">
                    @foreach($products as $product)
                    <!-- Product Card -->
                    <a href="{{ route('product.show', $product->slug) }}" class="product-card group bg-white dark:bg-gray-900 rounded-lg md:rounded-2xl p-3 border border-gray-100 dark:border-gray-800 flex flex-col relative h-full">
                        @if($product->created_at->diffInDays(now()) < 7)
                        <div class="absolute top-5 left-5 z-10 px-2 py-1 bg-brand-500 text-white text-[10px] font-bold rounded-md uppercase tracking-wider">{{ __('New') }}</div>
                        @endif
                        <!-- Image Wrapper -->
                        <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-gray-50 dark:bg-[#121212] mb-4">
                            <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <!-- Content -->
                        <div class="flex flex-col flex-1 justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200 line-clamp-2 leading-tight group-hover:text-brand-600 transition-colors">{{ $product->name }}</h3>
                                <div class="mt-2.5 flex items-baseline gap-2">
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">RM {{ number_format($product->price, 2) }}</span>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-500 font-medium">
                                <div class="flex items-center gap-1">
                                    <i class="ph-fill ph-star text-amber-400 text-sm"></i>
                                    <span class="text-gray-700 dark:text-gray-300 font-bold">{{ number_format($product->reviews_avg_rating ?? 0, 1) }}</span>
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

                <!-- Pagination -->
                <div class="mt-10 flex justify-center">
                    {{ $products->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 p-12 flex flex-col items-center justify-center text-center shadow-sm">
                    <div class="w-24 h-24 bg-gray-50 dark:bg-[#121212] rounded-full flex items-center justify-center text-gray-300 mb-6">
                        <i class="ph ph-magnifying-glass text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('No products found') }}</h3>
                    <p class="text-gray-500 dark:text-gray-500 mb-6 max-w-sm">{{ __('We could not find any products matching your current filters or search query.') }}</p>
                    <button wire:click="clearFilters" class="px-6 py-3 bg-brand-50 text-brand-600 font-semibold rounded-full hover:bg-brand-100 transition-colors">{{ __('Clear Filters') }}</button>
                </div>
            @endif

        </div>
    </div>
</div>
