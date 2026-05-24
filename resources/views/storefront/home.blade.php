@extends('layouts.storefront')

@section('content')
<main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        
    <!-- HERO BANNER (Modern Gradient & 3D Vibe) -->
    @if(isset($banners) && $banners->count() > 0)
        @php 
            $useCarousel = \App\Models\Setting::isEnabled('banner_carousel_enabled') && $banners->count() > 1; 
        @endphp

        @if($useCarousel)
            <div class="swiper banner-swiper relative w-full rounded-[2rem] overflow-hidden group">
                <div class="swiper-wrapper">
                    @foreach($banners as $mainBanner)
                        <div class="swiper-slide">
                            <section class="relative w-full aspect-square md:aspect-auto md:h-[350px] lg:h-[400px] bg-gray-900">
                                <!-- Dynamic Background -->
                                <div class="absolute inset-0 {{ $mainBanner->show_text_overlay ? 'bg-gray-900' : '' }} pointer-events-none">
                                    @if($mainBanner->youtube_link)
                                        @php
                                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $mainBanner->youtube_link, $match);
                                            $youtubeId = $match[1] ?? null;
                                        @endphp
                                        @if($youtubeId)
                                            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $youtubeId }}&playsinline=1" 
                                                    class="w-[100vw] min-w-[177.77vh] h-[56.25vw] min-h-[100vh] absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 {{ $mainBanner->show_text_overlay ? 'opacity-60' : '' }} pointer-events-none" 
                                                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                                        @endif
                                    @elseif($mainBanner->image)
                                        <img src="{{ Storage::disk('public')->url($mainBanner->mobile_image ?? $mainBanner->image) }}" class="block md:hidden w-full h-full object-cover {{ $mainBanner->show_text_overlay ? 'opacity-40' : '' }}">
                                        <img src="{{ Storage::disk('public')->url($mainBanner->image) }}" class="hidden md:block w-full h-full object-cover {{ $mainBanner->show_text_overlay ? 'opacity-40' : '' }}">
                                    @endif
                                </div>
                                <!-- Abstract decorative circles -->
                                <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-brand-500/20 blur-[100px] pointer-events-none"></div>
                                <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-brand-700/20 blur-[100px] pointer-events-none"></div>
                                
                                @if($mainBanner->show_text_overlay)
                                <div class="relative z-10 h-full flex items-center px-8 md:px-16 w-full">
                                    @if($mainBanner->html_content)
                                        <div class="w-full h-full flex flex-col justify-center">
                                            {!! $mainBanner->html_content !!}
                                        </div>
                                    @else
                                        <div class="max-w-2xl text-white space-y-4 md:space-y-6">
                                            @if($mainBanner->subtitle)
                                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-gray-900/10 backdrop-blur-md border border-white/20 text-xs font-semibold tracking-wider uppercase">
                                                <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                                                {{ $mainBanner->subtitle }}
                                            </div>
                                            @endif
                                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                                                {{ $mainBanner->title ?? __('Find What You Need, Faster.') }}
                                            </h1>
                                            @if($mainBanner->link && $mainBanner->button_text)
                                            <a href="{{ $mainBanner->link }}" class="mt-4 px-8 py-3.5 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-bold rounded-full hover:bg-gray-100 dark:bg-gray-800 transition-transform transform hover:scale-105 shadow-xl inline-flex items-center gap-2 w-max">
                                                {{ $mainBanner->button_text }} <i class="ph-bold ph-arrow-right text-brand-500"></i>
                                            </a>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if(isset($promoVoucher) && $mainBanner->show_voucher)
                                    <div class="hidden lg:block absolute right-16 top-1/2 transform -translate-y-1/2">
                                        <div class="w-72 h-80 bg-white dark:bg-gray-900/10 backdrop-blur-lg border border-white/20 rounded-3xl p-6 shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-500 flex flex-col justify-between">
                                            <div class="flex justify-between items-start">
                                                    <div class="w-12 h-12 bg-gradient-to-b from-brand-400 to-brand-500 rounded-2xl flex items-center justify-center shadow-lg"><i class="ph-fill ph-ticket text-white text-2xl"></i></div>
                                                    <span class="px-2 py-1 bg-accent-500/20 text-accent-300 text-xs font-bold rounded-lg backdrop-blur-sm border border-accent-500/30">{{ __('Voucher') }}</span>
                                            </div>
                                            <div>
                                                    <h3 class="text-2xl font-bold text-white mb-1">{{ $promoVoucher->code }}</h3>
                                                    <p class="text-white/80 text-sm mb-4">{{ $promoVoucher->type === 'percentage' ? $promoVoucher->value . '% OFF' : 'RM' . $promoVoucher->value . ' OFF' }}</p>
                                                    <button onclick="navigator.clipboard.writeText('{{ $promoVoucher->code }}'); alert('Code copied!')" class="w-full py-2.5 bg-white dark:bg-gray-900 text-brand-600 font-bold rounded-xl hover:bg-brand-50 transition-colors shadow-md">{{ __('Copy Code') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </section>
                        </div>
                    @endforeach
                </div>
                
                @if(\App\Models\Setting::isEnabled('banner_carousel_arrows'))
                    <div class="swiper-button-next !text-white !opacity-30 hover:!opacity-100 transition-opacity"></div>
                    <div class="swiper-button-prev !text-white !opacity-30 hover:!opacity-100 transition-opacity"></div>
                @endif
                <div class="swiper-pagination !bottom-4"></div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const effectType = '{{ \App\Models\Setting::get("banner_carousel_animation", "slide") }}';
                    const swiperParams = {
                        effect: effectType,
                        loop: true,
                        @if(\App\Models\Setting::isEnabled('banner_carousel_autoplay'))
                        autoplay: {
                            delay: 5000,
                            disableOnInteraction: false,
                        },
                        @endif
                        @if(\App\Models\Setting::isEnabled('banner_carousel_arrows'))
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },
                        @endif
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                    };

                    if (effectType === 'coverflow') {
                        swiperParams.coverflowEffect = {
                            rotate: 50, stretch: 0, depth: 100, modifier: 1, slideShadows: true,
                        };
                    } else if (effectType === 'cube') {
                        swiperParams.cubeEffect = {
                            shadow: true, slideShadows: true, shadowOffset: 20, shadowScale: 0.94,
                        };
                    }

                    new Swiper('.banner-swiper', swiperParams);
                });
            </script>
        @else
            <!-- Static Single Banner -->
            @php $mainBanner = $banners->first(); @endphp
            <section class="relative w-full aspect-square md:aspect-auto md:h-[350px] lg:h-[400px] rounded-[2rem] overflow-hidden bg-gray-900 group">
                <!-- Dynamic Background -->
                <div class="absolute inset-0 {{ $mainBanner->show_text_overlay ? 'bg-gray-900' : '' }} pointer-events-none">
                    @if($mainBanner->youtube_link)
                        @php
                            // Extract YouTube ID
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $mainBanner->youtube_link, $match);
                            $youtubeId = $match[1] ?? null;
                        @endphp
                        @if($youtubeId)
                            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $youtubeId }}&playsinline=1" 
                                    class="w-[100vw] min-w-[177.77vh] h-[56.25vw] min-h-[100vh] absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 {{ $mainBanner->show_text_overlay ? 'opacity-60' : '' }} pointer-events-none" 
                                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        @endif
                    @elseif($mainBanner->image)
                        <img src="{{ Storage::disk('public')->url($mainBanner->mobile_image ?? $mainBanner->image) }}" class="block md:hidden w-full h-full object-cover {{ $mainBanner->show_text_overlay ? 'opacity-40' : '' }}">
                        <img src="{{ Storage::disk('public')->url($mainBanner->image) }}" class="hidden md:block w-full h-full object-cover {{ $mainBanner->show_text_overlay ? 'opacity-40' : '' }}">
                    @endif
                </div>
                <!-- Abstract decorative circles -->
                <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-brand-500/20 blur-[100px] pointer-events-none"></div>
                <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-brand-700/20 blur-[100px] pointer-events-none"></div>
                
                @if($mainBanner->show_text_overlay)
                <div class="relative z-10 h-full flex items-center px-8 md:px-16 w-full">
                    @if($mainBanner->html_content)
                        <div class="w-full h-full flex flex-col justify-center">
                            {!! $mainBanner->html_content !!}
                        </div>
                    @else
                        <div class="max-w-2xl text-white space-y-4 md:space-y-6">
                            @if($mainBanner->subtitle)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-gray-900/10 backdrop-blur-md border border-white/20 text-xs font-semibold tracking-wider uppercase">
                                <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                                {{ $mainBanner->subtitle }}
                            </div>
                            @endif
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                                {{ $mainBanner->title ?? __('Find What You Need, Faster.') }}
                            </h1>
                            @if($mainBanner->link && $mainBanner->button_text)
                            <a href="{{ $mainBanner->link }}" class="mt-4 px-8 py-3.5 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-bold rounded-full hover:bg-gray-100 dark:bg-gray-800 transition-transform transform hover:scale-105 shadow-xl inline-flex items-center gap-2 w-max">
                                {{ $mainBanner->button_text }} <i class="ph-bold ph-arrow-right text-brand-500"></i>
                            </a>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Floating Element (Mockup/Illustration placeholder) -->
                    @if(isset($promoVoucher) && $mainBanner->show_voucher)
                    <div class="hidden lg:block absolute right-16 top-1/2 transform -translate-y-1/2">
                        <div class="w-72 h-80 bg-white dark:bg-gray-900/10 backdrop-blur-lg border border-white/20 rounded-3xl p-6 shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-500 flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                    <div class="w-12 h-12 bg-gradient-to-b from-brand-400 to-brand-500 rounded-2xl flex items-center justify-center shadow-lg"><i class="ph-fill ph-ticket text-white text-2xl"></i></div>
                                    <span class="px-2 py-1 bg-accent-500/20 text-accent-300 text-xs font-bold rounded-lg backdrop-blur-sm border border-accent-500/30">{{ __('Voucher') }}</span>
                            </div>
                            <div>
                                    <h3 class="text-2xl font-bold text-white mb-1">{{ $promoVoucher->code }}</h3>
                                    <p class="text-white/80 text-sm mb-4">{{ $promoVoucher->type === 'percentage' ? $promoVoucher->value . '% OFF' : 'RM' . $promoVoucher->value . ' OFF' }}</p>
                                    <button onclick="navigator.clipboard.writeText('{{ $promoVoucher->code }}'); alert('Code copied!')" class="w-full py-2.5 bg-white dark:bg-gray-900 text-brand-600 font-bold rounded-xl hover:bg-brand-50 transition-colors shadow-md">{{ __('Copy Code') }}</button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </section>
        @endif
    @endif

    <!-- FLASH SALE SECTION -->
    @if(isset($activeFlashSale))
    <section class="space-y-6 bg-gradient-to-br from-brand-600 to-brand-700 rounded-[2rem] p-6 md:p-8 text-white relative overflow-hidden">
        <!-- Abstract decorations -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white dark:bg-gray-900/10 rounded-full blur-[80px]"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white dark:bg-gray-900/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="ph-fill ph-lightning text-accent-400"></i> Flash Sale
                </div>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight">{{ $activeFlashSale->name }}</h2>
            </div>
            
            <!-- Alpine.js Countdown Timer -->
            <div x-data="countdown('{{ $activeFlashSale->ends_at->toIso8601String() }}')" class="flex items-center gap-2 md:gap-4">
                <div class="text-sm font-medium text-brand-100 hidden sm:block">{{ __('Ends in') }}</div>
                <div class="flex items-center gap-2 text-center">
                    <div class="bg-gray-900/40 backdrop-blur-sm px-3 py-2 rounded-xl min-w-[3rem]">
                        <span class="text-xl font-bold" x-text="days">00</span>
                        <div class="text-[10px] text-brand-200 uppercase font-bold">Hari</div>
                    </div>
                    <span class="font-bold text-xl">:</span>
                    <div class="bg-gray-900/40 backdrop-blur-sm px-3 py-2 rounded-xl min-w-[3rem]">
                        <span class="text-xl font-bold" x-text="hours">00</span>
                        <div class="text-[10px] text-brand-200 uppercase font-bold">Jam</div>
                    </div>
                    <span class="font-bold text-xl">:</span>
                    <div class="bg-gray-900/40 backdrop-blur-sm px-3 py-2 rounded-xl min-w-[3rem]">
                        <span class="text-xl font-bold" x-text="minutes">00</span>
                        <div class="text-[10px] text-brand-200 uppercase font-bold">Menit</div>
                    </div>
                    <span class="font-bold text-xl">:</span>
                    <div class="bg-gray-900/40 backdrop-blur-sm px-3 py-2 rounded-xl min-w-[3rem]">
                        <span class="text-xl font-bold text-accent-400" x-text="seconds">00</span>
                        <div class="text-[10px] text-brand-200 uppercase font-bold">Detik</div>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('flash-sale.index') }}" class="hidden md:flex items-center gap-1.5 text-sm font-semibold text-white hover:text-brand-100 transition-colors px-4 py-2 rounded-full hover:bg-white dark:bg-gray-900/10">
                {{ __('View all') }} <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        <div class="flex sm:grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-4 relative z-10 overflow-x-auto snap-inline hide-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0 pb-4">
            @foreach($activeFlashSale->products as $product)
            <a href="{{ route('product.show', $product->slug) }}" class="snap-item w-[140px] flex-shrink-0 sm:w-auto group bg-white dark:bg-gray-900 rounded-2xl p-3 flex flex-col relative h-full hover:-translate-y-1 transition-transform shadow-sm">
                <div class="absolute top-0 right-0 z-10 px-3 py-1 bg-accent-500 text-white text-[10px] font-bold rounded-bl-xl rounded-tr-2xl">
                    -{{ round((($product->price - $product->pivot->sale_price) / $product->price) * 100) }}%
                </div>
                <!-- Image -->
                <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-gray-50 dark:bg-[#121212] mb-3">
                    <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>
                <!-- Content -->
                <div class="flex flex-col flex-1 justify-between">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200 line-clamp-1 leading-tight">{{ $product->name }}</h3>
                    <div class="mt-1 flex flex-col">
                        <span class="text-lg font-extrabold text-brand-600">RM {{ number_format($product->pivot->sale_price, 2) }}</span>
                        <span class="text-xs text-gray-400 dark:text-gray-600 line-through font-medium">RM {{ number_format($product->price, 2) }}</span>
                    </div>
                    <!-- Stock Progress -->
                    <div class="mt-3">
                        @php
                            // Simulating sold percentage based on qty
                            $sold = max(0, 100 - ($product->pivot->qty * 5)); // Just a visual simulation for the frontend demo
                            if($sold > 95) $sold = 95; 
                        @endphp
                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 mb-1">
                            <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ $sold }}%"></div>
                        </div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-500 font-medium">Tersisa {{ $product->pivot->qty }} barang</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        
        <!-- Mobile View All -->
        <a href="{{ route('flash-sale.index') }}" class="flex md:hidden w-full items-center justify-center gap-1.5 text-sm font-bold text-white bg-white dark:bg-gray-900/20 py-3 rounded-xl hover:bg-white dark:bg-gray-900/30 transition-colors">
            {{ __('View all flash sales') }} <i class="ph-bold ph-arrow-right"></i>
        </a>
    </section>
    
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

    <!-- CATEGORY HIGHLIGHTS -->
    @if(isset($featuredCategories) && $featuredCategories->count() > 0)
    <section class="space-y-6 ">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Shop by') }} <span class="text-brand-500">{{ __('Category') }}</span></h2>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1 font-medium">{{ __('Explore our curated categories.') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="hidden sm:flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors px-4 py-2 rounded-full hover:bg-brand-50">
                {{ __('View all') }} <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        <div class="flex sm:grid sm:grid-cols-3 md:grid-cols-4 gap-4 overflow-x-auto snap-inline hide-scrollbar -mx-4 px-4 sm:-mx-6 sm:px-6 md:mx-0 md:px-0 pb-4">
            @php
                $categoryIcons = ['ph-t-shirt', 'ph-sneaker', 'ph-device-mobile', 'ph-laptop', 'ph-headphones', 'ph-watch', 'ph-handbag', 'ph-cooking-pot'];
                $categoryColors = [
                    ['bg-brand-50', 'text-brand-600', 'border-brand-100'],
                    ['bg-emerald-50', 'text-emerald-600', 'border-emerald-100'],
                    ['bg-violet-50', 'text-violet-600', 'border-violet-100'],
                    ['bg-amber-50', 'text-amber-600', 'border-amber-100'],
                    ['bg-rose-50', 'text-rose-600', 'border-rose-100'],
                    ['bg-cyan-50', 'text-cyan-600', 'border-cyan-100'],
                    ['bg-indigo-50', 'text-indigo-600', 'border-indigo-100'],
                    ['bg-orange-50', 'text-orange-600', 'border-orange-100'],
                ];
            @endphp
            @foreach($featuredCategories as $index => $cat)
                @php
                    $color = $categoryColors[$index % count($categoryColors)];
                    $icon = $categoryIcons[$index % count($categoryIcons)];
                @endphp
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="snap-item w-[140px] flex-shrink-0 sm:w-auto group {{ $color[0] }} border {{ $color[2] }} rounded-2xl p-5 flex flex-col items-center text-center hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="w-14 h-14 {{ $color[0] }} {{ $color[1] }} rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i class="ph {{ $icon }} text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">{{ $cat->name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-500 font-medium mt-1">{{ $cat->products_count }} {{ __('Products') }}</p>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- PRODUCT SECTION: FOR YOU -->
    <section class="space-y-6  pb-6 md:pb-0">
        <!-- Section Header -->
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ __('Recommended') }} <span class="text-brand-500">{{ __('For You') }}</span></h2>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1 font-medium">{{ __('Based on your recent activity and searches.') }}</p>
            </div>
            <a href="#" class="hidden sm:flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors px-4 py-2 rounded-full hover:bg-brand-50">
                {{ __('View all') }} <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 md:gap-6">
            @foreach($recommendedProducts as $product)
            <!-- Product Card -->
            <a href="{{ route('product.show', $product->slug) }}" class="product-card group bg-white dark:bg-gray-900 rounded-2xl p-3 border border-gray-100 dark:border-gray-800 flex flex-col relative h-full">
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
        
        <!-- Mobile View All Button -->
        <button class="w-full sm:hidden py-3 border border-gray-200 dark:border-gray-700 rounded-full text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-[#121212]">
            {{ __('View all recommendations') }}
        </button>
    </section>

</main>
@endsection
