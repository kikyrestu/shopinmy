@section('title', __('Product Bundles'))

<div class="bg-gray-50 dark:bg-[#121212] min-h-screen pb-16">
    <!-- Header -->
    <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white py-12 md:py-16 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-white dark:bg-gray-900/10 rounded-full blur-[80px]"></div>
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col items-center text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white dark:bg-gray-900/20 backdrop-blur-md rounded-full text-sm font-bold uppercase tracking-wider mb-4">
                <i class="ph-fill ph-package text-amber-200"></i> {{ __('Combo Deals') }}
            </div>
            
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">{{ __('Product Bundles') }}</h1>
            <p class="text-amber-50 text-lg max-w-2xl">{{ __('Buy more, save more. Grab our curated collections at a special discounted price.') }}</p>
        </div>
    </div>

    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-6 relative z-10">
        @if($bundles->isEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-12 text-center shadow-sm border border-gray-100 dark:border-gray-800">
                <i class="ph-fill ph-package text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('No bundles available right now') }}</h2>
                <p class="text-gray-500 dark:text-gray-500 mt-2">{{ __('Check back later for exciting combo deals!') }}</p>
            </div>
        @else
            <div class="space-y-8">
                @foreach($bundles as $bundle)
                    <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col md:flex-row gap-8">
                        
                        <!-- Bundle Details & Products -->
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $bundle->name }}</h2>
                            @if($bundle->description)
                                <p class="text-gray-500 dark:text-gray-500 mb-6">{{ $bundle->description }}</p>
                            @endif
                            
                            <div class="space-y-4">
                                <h3 class="text-sm font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider">{{ __('Includes') }}:</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @php $totalOriginalPrice = 0; @endphp
                                    @foreach($bundle->products as $product)
                                        @php $totalOriginalPrice += ($product->price * $product->pivot->qty); @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-[#121212]">
                                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-white dark:bg-gray-900 flex-shrink-0">
                                                @if($product->primaryImage)
                                                    <img src="{{ $product->first_image_url }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="ph ph-image text-xl"></i></div>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">{{ $product->name }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ $product->pivot->qty }}x</p>
                                                <p class="text-xs font-bold text-gray-400 dark:text-gray-600 line-through mt-1">RM {{ number_format($product->price, 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Add to Cart Sidebar -->
                        <div class="w-full md:w-80 bg-amber-50 rounded-2xl p-6 border border-amber-100 flex flex-col justify-center relative overflow-hidden">
                            <!-- Abstract decoration -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-200 rounded-full blur-[40px] opacity-50"></div>
                            
                            <div class="relative z-10">
                                <div class="mb-4">
                                    <p class="text-sm font-bold text-gray-500 dark:text-gray-500 mb-1">{{ __('Bundle Price') }}</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-black text-amber-600">RM {{ number_format($bundle->price, 2) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-sm font-bold text-gray-400 dark:text-gray-600 line-through">RM {{ number_format($totalOriginalPrice, 2) }}</span>
                                        <span class="px-2 py-0.5 bg-accent-500 text-white text-[10px] font-bold rounded-md">
                                            {{ __('Save') }} RM {{ number_format($totalOriginalPrice - $bundle->price, 2) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <button wire:click="addToCart({{ $bundle->id }})" wire:loading.attr="disabled" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl font-bold shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2 group">
                                    <i class="ph-bold ph-shopping-cart text-lg group-hover:scale-110 transition-transform"></i>
                                    <span wire:loading.remove wire:target="addToCart({{ $bundle->id }})">{{ __('Add Bundle to Cart') }}</span>
                                    <span wire:loading wire:target="addToCart({{ $bundle->id }})">{{ __('Adding...') }}</span>
                                </button>
                            </div>
                        </div>
                        
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</div>
