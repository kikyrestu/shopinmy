@extends('layouts.storefront')

@section('title', __('User Dashboard'))

@section('content')
<div class="bg-gray-50 dark:bg-[#121212] min-h-screen py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Sidebar -->
            <aside class="w-full lg:w-72 flex-shrink-0 mb-6 lg:mb-0 hidden lg:block">
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden sticky top-28">
                    <!-- User Profile Header -->
                    <div class="p-6 bg-gradient-to-br from-brand-50 to-white border-b border-gray-100 dark:border-gray-800 flex items-center gap-4">
                        <div class="w-14 h-14 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold text-xl overflow-hidden shadow-inner">
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="p-4 flex flex-row lg:flex-col gap-2 overflow-x-auto hide-scrollbar">
                        <a href="{{ route('dashboard') }}" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212] hover:text-gray-900 dark:text-gray-100' }}">
                            <i class="{{ request()->routeIs('dashboard') ? 'ph-fill' : 'ph' }} ph-squares-four text-xl"></i>
                            {{ __('Overview') }}
                        </a>
                        <a href="{{ route('dashboard.orders') }}" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard.orders') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212] hover:text-gray-900 dark:text-gray-100' }}">
                            <i class="{{ request()->routeIs('dashboard.orders') ? 'ph-fill' : 'ph' }} ph-package text-xl"></i>
                            {{ __('My Orders') }}
                        </a>
                        <a href="{{ route('dashboard.addresses') }}" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard.addresses') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212] hover:text-gray-900 dark:text-gray-100' }}">
                            <i class="{{ request()->routeIs('dashboard.addresses') ? 'ph-fill' : 'ph' }} ph-map-pin text-xl"></i>
                            {{ __('Address Book') }}
                        </a>
                        <a href="{{ route('dashboard.wishlist') }}" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard.wishlist') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212] hover:text-gray-900 dark:text-gray-100' }}">
                            <i class="{{ request()->routeIs('dashboard.wishlist') ? 'ph-fill' : 'ph' }} ph-heart text-xl"></i>
                            {{ __('My Wishlist') }}
                        </a>
                        @php
                            $loyaltyOn = \App\Models\Setting::get('loyalty_enabled', true);
                            $referralOn = \App\Models\Setting::get('referral_enabled', true);
                        @endphp
                        @if($loyaltyOn || $referralOn)
                        <a href="{{ route('dashboard.loyalty') }}" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard.loyalty') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212] hover:text-gray-900 dark:text-gray-100' }}">
                            <i class="{{ request()->routeIs('dashboard.loyalty') ? 'ph-fill' : 'ph' }} ph-star text-xl"></i>
                            {{ __('Loyalty & Referrals') }}
                        </a>
                        @endif
                        <a href="{{ route('dashboard.profile') }}" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard.profile') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:bg-[#121212] hover:text-gray-900 dark:text-gray-100' }}">
                            <i class="{{ request()->routeIs('dashboard.profile') ? 'ph-fill' : 'ph' }} ph-user text-xl"></i>
                            {{ __('My Profile') }}
                        </a>
                        <div class="lg:pt-4 lg:mt-2 lg:border-t border-gray-100 dark:border-gray-800 flex items-center">
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-red-500 hover:bg-red-50 hover:text-red-600">
                                    <i class="ph ph-sign-out text-xl"></i>
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        </div>
                    </nav>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
                @yield('dashboard_content')
            </main>

        </div>
    </div>
</div>
@endsection
