@section('title', __('Flash Sale'))

<div class="bg-gray-50 min-h-screen pb-16">
    <!-- Header -->
    <div class="bg-gradient-to-r from-brand-600 to-brand-700 text-white py-12 md:py-16 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-[80px]"></div>
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col items-center text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-sm font-bold uppercase tracking-wider mb-4">
                <i class="ph-fill ph-lightning text-accent-400"></i> {{ __('Flash Sale') }}
            </div>
            
            @if($activeFlashSale)
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">{{ $activeFlashSale->name }}</h1>
                <p class="text-brand-100 text-lg max-w-2xl">{{ $activeFlashSale->description ?? __('Grab these amazing deals before time runs out!') }}</p>
                
                <!-- Countdown -->
                <div class="mt-8" x-data="countdown('{{ $activeFlashSale->ends_at->toIso8601String() }}')">
                    <div class="flex items-center justify-center gap-2 text-center">
                        <div class="bg-gray-900/40 backdrop-blur-md px-4 py-3 rounded-2xl min-w-[4rem] border border-white/10">
                            <span class="text-2xl md:text-3xl font-bold" x-text="days">00</span>
                            <div class="text-xs text-brand-200 uppercase font-bold mt-1">Hari</div>
                        </div>
                        <span class="font-bold text-2xl">:</span>
                        <div class="bg-gray-900/40 backdrop-blur-md px-4 py-3 rounded-2xl min-w-[4rem] border border-white/10">
                            <span class="text-2xl md:text-3xl font-bold" x-text="hours">00</span>
                            <div class="text-xs text-brand-200 uppercase font-bold mt-1">Jam</div>
                        </div>
                        <span class="font-bold text-2xl">:</span>
                        <div class="bg-gray-900/40 backdrop-blur-md px-4 py-3 rounded-2xl min-w-[4rem] border border-white/10">
                            <span class="text-2xl md:text-3xl font-bold" x-text="minutes">00</span>
                            <div class="text-xs text-brand-200 uppercase font-bold mt-1">Menit</div>
                        </div>
                        <span class="font-bold text-2xl">:</span>
                        <div class="bg-gray-900/40 backdrop-blur-md px-4 py-3 rounded-2xl min-w-[4rem] border border-white/10">
                            <span class="text-2xl md:text-3xl font-bold text-accent-400" x-text="seconds">00</span>
                            <div class="text-xs text-brand-200 uppercase font-bold mt-1">Detik</div>
                        </div>
                    </div>
                </div>
            @else
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">{{ __('No Active Flash Sale') }}</h1>
                <p class="text-brand-100 text-lg max-w-2xl">{{ __('Stay tuned for our next exciting deals!') }}</p>
            @endif
        </div>
    </div>

    @if($activeFlashSale && $activeFlashSale->products->isNotEmpty())
    <main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-6">
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                @foreach($activeFlashSale->products as $product)
                <a href="{{ route('product.show', $product->slug) }}" class="product-card group bg-white rounded-2xl p-3 border border-gray-100 flex flex-col relative h-full hover:shadow-lg transition-all duration-300">
                    <div class="absolute top-5 right-5 z-10 px-2 py-1 bg-accent-500 text-white text-xs font-bold rounded-md">
                        -{{ round((($product->price - $product->pivot->sale_price) / $product->price) * 100) }}%
                    </div>
                    <!-- Image -->
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-gray-50 mb-4">
                        @if($product->primaryImage)
                            <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @elseif(!empty($product->images) && isset($product->images[0]))
                            <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                <i class="ph ph-image text-4xl"></i>
                            </div>
                        @endif
                    </div>
                    <!-- Content -->
                    <div class="flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-800 line-clamp-2 leading-tight group-hover:text-brand-600 transition-colors">{{ $product->name }}</h3>
                            <div class="mt-2.5 flex flex-col">
                                <span class="text-xl font-black text-brand-600">RM {{ number_format($product->pivot->sale_price, 2) }}</span>
                                <span class="text-xs text-gray-400 line-through font-medium">RM {{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                        <!-- Stock Progress -->
                        <div class="mt-4 pt-3 border-t border-gray-50">
                            @php
                                $sold = max(0, 100 - ($product->pivot->qty * 5)); 
                                if($sold > 95) $sold = 95; 
                            @endphp
                            <div class="flex justify-between text-[11px] text-gray-500 font-bold mb-1">
                                <span>{{ __('Telah Terjual') }}</span>
                                <span class="text-brand-600">{{ $sold }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-brand-500 to-accent-500 h-2 rounded-full" style="width: {{ $sold }}%"></div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </main>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('countdown', (endTime) => ({
                days: '00',
                hours: '00',
                minutes: '00',
                seconds: '00',
                init() {
                    const end = new Date(endTime).getTime();
                    setInterval(() => {
                        const now = new Date().getTime();
                        const distance = end - now;
                        
                        if (distance < 0) return;
                        
                        this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                        this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                        this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                        this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                    }, 1000);
                }
            }));
        });
    </script>
    @endif
</div>
