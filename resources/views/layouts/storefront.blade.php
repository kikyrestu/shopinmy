<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO Meta Tags -->
    @php
        $defaultTitle = \App\Models\Setting::get('site_name', 'NexShop');
        $defaultDesc = \App\Models\Setting::get('site_description', __('A next-generation e-commerce platform delivering a seamless, secure, and user-centric shopping experience.'));
        $defaultLogo = \App\Models\Setting::get('site_logo') ? Storage::url(\App\Models\Setting::get('site_logo')) : asset('logo.png');
    @endphp
    
    <title>@yield('title', $defaultTitle . ' - Modern E-Commerce')</title>
    <meta name="description" content="@yield('meta_description', $defaultDesc)">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="@yield('meta_title', $defaultTitle)">
    <meta property="og:description" content="@yield('meta_description', $defaultDesc)">
    <meta property="og:image" content="@yield('meta_image', $defaultLogo)">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', $defaultTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $defaultDesc)">
    <meta name="twitter:image" content="@yield('meta_image', $defaultLogo)">
    
    <!-- Dynamic Favicon -->
    @if(\App\Models\Setting::get('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ Storage::url(\App\Models\Setting::get('site_favicon')) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phosphor Icons (Sleek, modern iconography) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Swiper.js for Carousels -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Alpine.js is bundled with Livewire 3, so we don't load it manually -->
    <!-- Google Fonts: Plus Jakarta Sans (Popular in Web3/Fintech) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981', // Web3 Emerald Green
                            600: '#059669',
                            900: '#064e3b',
                        },
                        accent: {
                            300: '#fdba74',
                            500: '#f97316', // Vibrant Orange
                        }
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                        'floating': '0 20px 40px -15px rgba(0,0,0,0.05)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #FAFAFC;
        }
        .dark body {
            background-color: #121212;
            color: #ffffff;
        }
        
        /* Smooth scrolling for anchor links */
        html { scroll-behavior: smooth; }

        /* Hide scrollbar for category tabs but keep functionality */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Product Card Hover Effect */
        .product-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
        }
        /* Mobile Native Feel Utilities */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        .snap-inline { scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; }
        .snap-item { scroll-snap-align: start; }
    </style>
    @stack('styles')
</head>
<body class="text-gray-800 dark:text-gray-200 antialiased pb-20 md:pb-0 overflow-x-hidden">

    <!-- ==========================================
         GLASSMORPHISM HEADER (WEB3 STYLE)
         ========================================== -->
    <header id="smart-header" class="sticky top-0 z-50 bg-white dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800/50 shadow-sm transition-transform duration-300 transform translate-y-0">
        <!-- Top Bar -->
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4 lg:gap-8">
                
                <!-- Logo -->
                <a href="{{ route('home') }}" class="hidden md:flex flex-shrink-0 items-center gap-2 cursor-pointer">
                    @if(\App\Models\Setting::get('site_logo'))
                        <img src="{{ Storage::url(\App\Models\Setting::get('site_logo')) }}" alt="{{ \App\Models\Setting::get('site_name', 'NexShop') }}" class="h-10 w-auto object-contain">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-b from-brand-400 to-brand-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                            <i class="ph-bold ph-shopping-bag text-xl"></i>
                        </div>
                    @endif
                    <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 hidden sm:block">{{ \App\Models\Setting::get('site_name', 'NexShop') }}</span>
                </a>

                <!-- Command-Palette Style Search -->
                <div class="flex-1 w-full max-w-3xl relative block">
                    <form action="/products" method="GET" class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 md:pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-magnifying-glass text-lg md:text-xl text-gray-400 dark:text-gray-600 group-focus-within:text-brand-500 transition-colors"></i>
                        </div>
                        <input type="text" name="search" class="block w-full pl-10 md:pl-12 pr-4 py-2.5 md:py-3 bg-gray-100 dark:bg-gray-800/80 border border-transparent rounded-full text-sm placeholder-gray-400 focus:bg-white dark:bg-gray-900 focus:border-brand-500/30 focus:ring-4 focus:ring-brand-500/10 transition-all outline-none" placeholder="{{ __('Search brands, products, or stores...') }}">
                        <div class="absolute inset-y-0 right-0 pr-1 md:pr-2 flex items-center">
                            <button type="submit" class="p-1.5 bg-brand-500 text-white rounded-full hover:bg-brand-600 transition-colors shadow-sm">
                                <i class="ph-bold ph-arrow-right text-xs md:text-sm w-4 h-4 md:w-5 md:h-5 flex items-center justify-center"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Icons & Auth -->
                <div class="flex items-center justify-end gap-1 sm:gap-3">
                    <div class="hidden md:block">
                        @livewire('storefront.cart-badge')
                    </div>
                    <button class="p-2.5 text-gray-500 dark:text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-all hidden sm:block relative">
                        <i class="ph ph-heart text-2xl"></i>
                        @auth
                            @php $wishlistCount = auth()->user()->wishlists()->count(); @endphp
                            @if($wishlistCount > 0)
                                <span class="absolute top-1 right-1 w-4 h-4 bg-accent-500 rounded-full ring-2 ring-white text-[10px] text-white font-bold flex items-center justify-center">{{ $wishlistCount }}</span>
                            @endif
                        @endauth
                    </button>
                    @auth
                        <livewire:storefront.header-notifications />
                    @else
                        <button class="p-2.5 text-gray-500 dark:text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-all hidden lg:block" onclick="window.location.href='{{ route('login') }}'">
                            <i class="ph ph-bell text-2xl"></i>
                        </button>
                    @endauth

                    <!-- Language Switcher -->
                    <div class="hidden sm:flex items-center bg-gray-100 dark:bg-gray-800 rounded-full p-0.5">
                        <a href="{{ route('locale.switch', 'ms') }}" class="px-3 py-1 text-xs font-semibold rounded-full transition-all {{ app()->getLocale() === 'ms' ? 'bg-white dark:bg-gray-900 text-brand-600 shadow-sm' : 'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300' }}">MS</a>
                        <a href="{{ route('locale.switch', 'en') }}" class="px-3 py-1 text-xs font-semibold rounded-full transition-all {{ app()->getLocale() === 'en' ? 'bg-white dark:bg-gray-900 text-brand-600 shadow-sm' : 'text-gray-500 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300' }}">EN</a>
                    </div>
                    
                    <div class="h-6 w-px bg-gray-200 mx-1 hidden sm:block"></div>
                    
                    <!-- Modern Auth Buttons -->
                    <div class="hidden md:flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 pl-1.5 pr-4 py-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:bg-gray-800 rounded-full transition-colors border border-transparent hover:border-gray-200 dark:border-gray-700">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover">
                                {{ __('My Profile') }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-b from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-full shadow-lg shadow-red-500/25 transition-all transform hover:-translate-y-0.5 inline-block">{{ __('Logout') }}</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:bg-gray-800 rounded-full transition-colors">{{ __('Login') }}</a>
                            <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-b from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 rounded-full shadow-lg shadow-brand-500/25 transition-all transform hover:-translate-y-0.5 inline-block">{{ __('Register') }}</a>
                        @endauth
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="hidden p-2 text-gray-600 dark:text-gray-400">
                        <i class="ph ph-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Secondary Navigation (Categories & Location) -->
        <div class="border-t border-gray-100 dark:border-gray-800/50 bg-white dark:bg-gray-900/40">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between text-sm">
                
                <div class="flex items-center gap-4 lg:gap-8 flex-1 min-w-0">
                    <!-- Category Dropdown (Mega Menu) -->
                    <div x-data="{ open: false, activeCategory: {{ $categories->first()?->id ?? 'null' }} }" class="relative hidden md:block">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-800/80 rounded-lg text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200 transition-colors whitespace-nowrap">
                            <i class="ph ph-squares-four text-lg"></i> {{ __('Kategori') }}
                        </button>
                        
                        <!-- Mega Menu Container -->
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 -translate-y-2" 
                             x-transition:enter-end="opacity-100 translate-y-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 translate-y-0" 
                             x-transition:leave-end="opacity-0 -translate-y-2" 
                             class="absolute left-0 top-full mt-3 w-[800px] bg-white dark:bg-gray-900 rounded-xl shadow-xl border border-gray-100 dark:border-gray-800 flex z-50 overflow-hidden" style="display: none; min-height: 350px;">
                            
                            <!-- Sidebar Kiri -->
                            <div class="w-1/3 bg-gray-50 dark:bg-[#121212]/50 border-r border-gray-100 dark:border-gray-800 py-3 flex flex-col">
                                @foreach($categories as $cat)
                                @php
                                    // Custom icons based on category slug if available, fallback to default
                                    $icon = match($cat->slug) {
                                        'promo' => 'ph-megaphone',
                                        'kebutuhan' => 'ph-shopping-basket',
                                        'aksesoris' => 'ph-armchair',
                                        'sport' => 'ph-sneaker',
                                        'untukmu' => 'ph-star',
                                        default => 'ph-tag'
                                    };
                                    $color = match($cat->slug) {
                                        'promo' => 'text-red-500',
                                        'kebutuhan' => 'text-blue-500',
                                        'aksesoris' => 'text-amber-500',
                                        'sport' => 'text-emerald-500',
                                        'untukmu' => 'text-gray-700 dark:text-gray-300',
                                        default => 'text-gray-500 dark:text-gray-500'
                                    };
                                @endphp
                                <button @mouseenter="activeCategory = {{ $cat->id }}" 
                                        :class="activeCategory === {{ $cat->id }} ? 'bg-white dark:bg-gray-900 shadow-sm font-bold text-brand-600' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:bg-gray-800 font-medium'" 
                                        class="flex items-center gap-3 px-5 py-3 text-sm transition-all text-left w-full relative">
                                    <i class="ph-fill {{ $icon }} {{ $color }} text-xl"></i>
                                    {{ $cat->name }}
                                </button>
                                @endforeach
                            </div>

                            <!-- Area Kanan -->
                            <div class="w-2/3 p-6 flex flex-col justify-between">
                                <div class="flex-1">
                                    @foreach($categories as $cat)
                                    <div x-show="activeCategory === {{ $cat->id }}" class="h-full flex flex-col">
                                        <!-- Header Kategori -->
                                        <div class="flex justify-between items-center mb-2 pb-3 border-b border-gray-100 dark:border-gray-800">
                                            <div>
                                                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">{{ $cat->name }}</h3>
                                                <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">{{ __('Choose a category to explore products faster.') }}</p>
                                            </div>
                                            <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="text-brand-600 hover:text-brand-700 text-sm font-bold flex items-center gap-1 transition-colors">
                                                {{ __('View all') }} <i class="ph-bold ph-arrow-right"></i>
                                            </a>
                                        </div>

                                        <!-- Konten Subkategori atau Info -->
                                        <div class="flex-1 mt-4">
                                            <div class="flex gap-6 h-full">
                                                <div class="flex-1">
                                                    @if($cat->children->count() > 0)
                                                        <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                                            @foreach($cat->children as $child)
                                                                <a href="{{ route('products.index', ['category' => $child->slug]) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors flex items-center gap-2">
                                                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                                                                    {{ $child->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="bg-gray-50 dark:bg-[#121212]/80 border border-gray-200 dark:border-gray-700 border-dashed rounded-xl p-4 flex items-center gap-3 text-gray-500 dark:text-gray-500 text-sm mt-2">
                                                            <i class="ph ph-info text-xl text-gray-400 dark:text-gray-600"></i>
                                                            {{ __('No subcategories available for this category.') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Featured Image -->
                                                @if($cat->image)
                                                <div class="w-40 flex-shrink-0">
                                                    <div class="w-full aspect-square rounded-2xl overflow-hidden bg-gray-50 dark:bg-[#121212] border border-gray-100 dark:border-gray-800 shadow-sm p-2 flex items-center justify-center">
                                                        <img src="{{ Storage::url($cat->image) }}" class="w-full h-full object-contain" alt="{{ $cat->name }}">
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location Dropdown -->
                    <div x-data="{ open: false }" class="relative hidden sm:block">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-800/80 rounded-full text-gray-500 dark:text-gray-500 cursor-pointer hover:bg-gray-200 transition-colors whitespace-nowrap">
                            <i class="ph-fill ph-map-pin text-brand-500"></i>
                            <span class="truncate max-w-[200px] text-xs font-medium">
                                @auth
                                    @php
                                        $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first() ?? auth()->user()->addresses()->first();
                                    @endphp
                                    {{ __('Ship to') }}: {{ $defaultAddress ? $defaultAddress->city . ', ' . $defaultAddress->state : __('Select Location') }}
                                @else
                                    {{ __('Ship to') }}: {{ __('Malaysia') }}
                                @endauth
                            </span>
                            <i class="ph ph-caret-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="absolute left-0 top-full mt-3 w-72 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 py-2 z-50" style="display: none;">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 mb-1">
                                <p class="text-xs font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider">{{ __('Shipping Address') }}</p>
                            </div>
                            @auth
                                @php $userAddresses = auth()->user()->addresses()->take(3)->get(); @endphp
                                @forelse($userAddresses as $addr)
                                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-brand-50 transition-colors cursor-pointer group">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $addr->is_default ? 'bg-brand-100 text-brand-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600' }}">
                                            <i class="ph-fill {{ strtolower($addr->label ?? '') == 'office' ? 'ph-buildings' : 'ph-house' }} text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                                {{ $addr->label ?? __('Address') }}
                                                @if($addr->is_default)
                                                    <span class="text-[10px] bg-brand-500 text-white px-1.5 py-0.5 rounded-md font-bold">{{ __('Default') }}</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 truncate mt-0.5">{{ $addr->address }}, {{ $addr->city }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-4 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-500">{{ __('No saved addresses.') }}</p>
                                    </div>
                                @endforelse
                                <div class="border-t border-gray-100 dark:border-gray-800 mt-1 pt-1">
                                    <a href="{{ route('dashboard.addresses') }}" class="flex items-center gap-2 px-4 py-2.5 hover:bg-brand-50 transition-colors text-brand-600 font-semibold text-sm">
                                        <i class="ph-bold ph-plus"></i> {{ __('Manage Addresses') }}
                                    </a>
                                </div>
                            @else
                                <div class="px-4 py-4 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mb-3">{{ __('Login to manage your shipping addresses.') }}</p>
                                    <a href="{{ route('login') }}" class="inline-block px-5 py-2 bg-brand-600 text-white text-sm font-bold rounded-full hover:bg-brand-700 transition-colors">{{ __('Login') }}</a>
                                </div>
                            @endauth
                        </div>
                    </div>

                    <!-- Scrollable Tabs (Web3 Pill Style) -->
                    <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar flex-1 pl-2 pb-1">
                        <a href="{{ route('home') }}" class="px-4 py-1.5 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-semibold whitespace-nowrap shadow-sm shadow-brand-500/20 transition-colors">{{ __('For You') }}</a>
                        <a href="{{ route('flash-sale.index') }}" class="px-4 py-1.5 rounded-full hover:bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium transition-colors whitespace-nowrap"><span class="text-accent-500">⚡</span> {{ __('Flash Sale') }}</a>
                        @foreach($categories as $category)
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="px-4 py-1.5 rounded-full hover:bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium transition-colors whitespace-nowrap">{{ $category->name }}</a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- ==========================================
         MAIN CONTENT
         ========================================== -->
    @yield('content')

    <!-- ==========================================
         MODERN FOOTER
         ========================================== -->
    <footer class="hidden md:block bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 pt-12 pb-24 md:pb-8 mt-auto">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
                
                <!-- Pilar 1: Brand & Sosial Media -->
                <div class="col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        @if(\App\Models\Setting::get('site_logo'))
                            <img src="{{ Storage::url(\App\Models\Setting::get('site_logo')) }}" alt="{{ \App\Models\Setting::get('site_name', 'NexShop') }}" class="h-8 w-auto object-contain">
                        @else
                            <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center text-white">
                                <i class="ph-bold ph-shopping-bag"></i>
                            </div>
                        @endif
                        <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ \App\Models\Setting::get('site_name', 'NexShop') }}</span>
                    </div>
                    <p class="text-gray-500 dark:text-gray-500 text-sm leading-relaxed mb-6">
                        {{ \App\Models\Setting::get('site_description', __('A next-generation e-commerce platform delivering a seamless, secure, and user-centric shopping experience.')) }}
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ \App\Models\Setting::get('instagram_url', '#') }}" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-[#121212] flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-brand-50 hover:text-brand-600 transition-colors"><i class="ph-fill ph-instagram-logo text-xl"></i></a>
                        <a href="{{ \App\Models\Setting::get('twitter_url', '#') }}" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-[#121212] flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-brand-50 hover:text-brand-600 transition-colors"><i class="ph-fill ph-twitter-logo text-xl"></i></a>
                        <a href="{{ \App\Models\Setting::get('facebook_url', '#') }}" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-[#121212] flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-brand-50 hover:text-brand-600 transition-colors"><i class="ph-fill ph-facebook-logo text-xl"></i></a>
                    </div>
                </div>

                <!-- Pilar 2: Quick Links -->
                <div class="col-span-1">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('Help Center') }}</h4>
                    <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-500">
                        @if(isset($footerPages) && count($footerPages) > 0)
                            @foreach($footerPages as $page)
                            <li><a href="/pages/{{ $page->slug }}" class="hover:text-brand-600 transition-colors">{{ $page->title }}</a></li>
                            @endforeach
                        @else
                            <li><a href="{{ \App\Models\Setting::get('help_center_url', '#') }}" class="hover:text-brand-600 transition-colors">{{ __('Help Center') }}</a></li>
                            <li><a href="{{ \App\Models\Setting::get('terms_conditions_url', '#') }}" class="hover:text-brand-600 transition-colors">{{ __('Terms & Conditions') }}</a></li>
                            <li><a href="{{ \App\Models\Setting::get('privacy_policy_url', '#') }}" class="hover:text-brand-600 transition-colors">{{ __('Privacy Policy') }}</a></li>
                            <li><a href="{{ \App\Models\Setting::get('track_order_url', '#') }}" class="hover:text-brand-600 transition-colors">{{ __('Track Order') }}</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Pilar 3: Keamanan -->
                <div class="col-span-1">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('Security & Payment') }}</h4>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mb-4">
                        {{ __('100% secure payment processing with encryption (Secure Checkout).') }}
                    </p>
                    <div class="flex items-center gap-3 text-emerald-600">
                        <i class="ph-fill ph-shield-check text-3xl"></i>
                        <span class="text-sm font-bold">{{ __('Safe & Secure Payments') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="pt-6 border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400 dark:text-gray-600 font-medium">
                <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'NexShop') }}. {{ __('All rights reserved.') }}</p>
                <div class="flex items-center gap-1">
                    {!! \App\Models\Setting::get('footer_tagline', __('Made with <i class="ph-fill ph-heart text-red-500 mx-1"></i> in Malaysia')) !!}
                </div>
            </div>
        </div>
    </footer>

    @include('storefront.whatsapp-button')
    
    <!-- ==========================================
         MOBILE BOTTOM NAVIGATION (APP-LIKE UX)
         ========================================== -->
    <div class="fixed bottom-0 left-0 z-50 w-full h-14 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 md:hidden flex justify-around items-center px-1 pb-safe shadow-[0_-4px_20px_rgba(0,0,0,0.03)]">
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('home') ? 'text-brand-600' : 'text-gray-400 dark:text-gray-600 hover:text-brand-500' }} transition">
            <i class="{{ request()->routeIs('home') ? 'ph-fill' : 'ph' }} ph-house text-[22px] mb-0.5"></i>
            <span class="text-[9px] font-semibold">{{ __('Home') }}</span>
        </a>
        <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('products.index') ? 'text-brand-600' : 'text-gray-400 dark:text-gray-600 hover:text-brand-500' }} transition">
            <i class="{{ request()->routeIs('products.index') ? 'ph-fill' : 'ph' }} ph-magnifying-glass text-[22px] mb-0.5"></i>
            <span class="text-[9px] font-medium">{{ __('Search') }}</span>
        </a>
        <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('cart.index') ? 'text-brand-600' : 'text-gray-400 dark:text-gray-600 hover:text-brand-500' }} transition relative">
            <div class="relative">
                <i class="{{ request()->routeIs('cart.index') ? 'ph-fill' : 'ph' }} ph-shopping-cart text-[22px] mb-0.5"></i>
                <livewire:storefront.cart-badge :minimal="true" />
            </div>
            <span class="text-[9px] font-medium">{{ __('Cart') }}</span>
        </a>
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('dashboard*') ? 'text-brand-600' : 'text-gray-400 dark:text-gray-600 hover:text-brand-500' }} transition">
            <i class="{{ request()->routeIs('dashboard*') ? 'ph-fill' : 'ph' }} ph-user text-[22px] mb-0.5"></i>
            <span class="text-[9px] font-medium">{{ __('Account') }}</span>
        </a>
    </div>

    @stack('scripts')

    <!-- Smart Header Auto-Hide Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lastScrollTop = 0;
            const header = document.getElementById('smart-header');
            
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Prevent hiding when at the very top (e.g. mobile bouncing)
                if (scrollTop <= 50) {
                    header.style.transform = 'translateY(0)';
                    return;
                }
                
                if (scrollTop > lastScrollTop) {
                    // Scroll Down -> Hide
                    header.style.transform = 'translateY(-100%)';
                } else {
                    // Scroll Up -> Show
                    header.style.transform = 'translateY(0)';
                }
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
            }, { passive: true });
        });
    </script>
</body>
</html>
