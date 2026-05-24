<div class="space-y-4 md:space-y-8 pb-20 md:pb-10 bg-gray-50 md:bg-transparent min-h-screen md:min-h-0">
    
    <!-- Mobile Native Profile Header -->
    <div class="bg-white md:bg-transparent p-4 md:p-0 flex items-center justify-between border-b border-gray-100 md:border-none">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 md:w-16 md:h-16 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold text-xl overflow-hidden shadow-inner flex-shrink-0">
                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
            </div>
            <div>
                <div class="font-extrabold text-gray-900 text-base md:text-2xl">{{ auth()->user()->name }}</div>
                <div class="text-xs md:text-sm text-gray-500 flex items-center gap-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('Active Member') }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <!-- Dark Mode Toggle Button (Alpine) -->
            <button @click="darkMode = !darkMode" class="text-gray-500 hover:text-brand-600 transition-colors">
                <i class="ph text-2xl" :class="darkMode ? 'ph-sun' : 'ph-moon'"></i>
            </button>
            <a href="{{ route('dashboard.profile') }}" class="text-gray-500 hover:text-brand-600 transition-colors">
                <i class="ph ph-gear text-2xl"></i>
            </a>
        </div>
    </div>

    <!-- Promo Banner -->
    <div class="px-4 md:px-0">
        <div class="bg-gradient-to-r from-emerald-50 to-brand-50 border border-emerald-100 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-600">{{ __('Yuk, lengkapi profilmu!') }}</div>
                <div class="font-bold text-brand-700 text-sm">{{ __('Dapatkan pengalaman terbaik') }}</div>
            </div>
            <a href="{{ route('dashboard.profile') }}" class="text-xs font-bold bg-brand-600 text-white px-3 py-1.5 rounded-full">{{ __('Lengkapi') }}</a>
        </div>
    </div>

    <!-- Transaksi Menu -->
    <div class="bg-white md:rounded-3xl md:border border-y md:border-y border-gray-100 p-4 md:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-extrabold text-gray-900 text-lg">{{ __('Transaksi') }}</h2>
            <a href="{{ route('dashboard.orders') }}" class="text-xs font-bold text-brand-600">{{ __('Lihat Semua') }}</a>
        </div>
        <div class="grid grid-cols-4 md:grid-cols-5 gap-2">
            <a href="{{ route('dashboard.orders') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
                    <i class="ph ph-wallet text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Belum Bayar') }}</span>
            </a>
            <a href="{{ route('dashboard.orders') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
                    <i class="ph ph-package text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Diproses') }}</span>
            </a>
            <a href="{{ route('dashboard.orders') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
                    <i class="ph ph-truck text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Dikirim') }}</span>
            </a>
            <a href="{{ route('dashboard.orders') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
                    <i class="ph ph-star text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Ulasan') }}</span>
            </a>
            <a href="{{ route('dashboard.loyalty') }}" class="hidden md:flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-12 h-12 bg-gray-50 text-amber-500 rounded-2xl flex items-center justify-center group-hover:bg-amber-50 transition-colors">
                    <i class="ph-fill ph-coin text-2xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600">{{ auth()->user()->loyaltyPoints()->sum('points') ?? 0 }} Poin</span>
            </a>
        </div>
    </div>

    <!-- Menu Lainnya -->
    <div class="bg-white md:rounded-3xl md:border border-y md:border-y border-gray-100 p-4 md:p-6 shadow-sm">
        <h2 class="font-extrabold text-gray-900 text-lg mb-4">{{ __('Menu Lainnya') }}</h2>
        <div class="grid grid-cols-4 md:grid-cols-5 gap-y-6 gap-x-2">
            <a href="{{ route('dashboard.wishlist') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-heart text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Wishlist') }}</span>
            </a>
            <a href="{{ route('dashboard.addresses') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-map-pin text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Address Book') }}</span>
            </a>
            @php
                $loyaltyOn = \App\Models\Setting::get('loyalty_enabled', true);
            @endphp
            @if($loyaltyOn)
            <a href="{{ route('dashboard.loyalty') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-star text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Loyalty') }}</span>
            </a>
            @endif
            <a href="{{ route('dashboard.profile') }}" class="flex flex-col items-center justify-start gap-2 group text-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="ph-fill ph-user text-2xl"></i>
                </div>
                <span class="text-[10px] md:text-xs font-medium text-gray-600">{{ __('Edit Profile') }}</span>
            </a>
        </div>
    </div>

    <!-- Recent Orders (Desktop View Keeps It) -->
    <div class="hidden md:block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">{{ __('Recent Orders') }}</h2>
            <a href="{{ route('dashboard.orders') }}" class="text-brand-600 hover:text-brand-700 font-semibold text-sm">{{ __('View All') }}</a>
        </div>
        
        @if($recentOrders->isEmpty())
            <div class="p-10 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-4">
                    <i class="ph ph-package text-4xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('No orders yet') }}</h3>
                <p class="text-gray-500">{{ __('When you place an order, it will appear here.') }}</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block px-6 py-2.5 bg-brand-600 text-white font-bold rounded-full hover:bg-brand-700 transition-colors">{{ __('Start Shopping') }}</a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($recentOrders as $order)
                <div class="p-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-gray-900 mb-1">{{ $order->order_number }}</div>
                        <div class="text-sm text-gray-500">{{ $order->created_at->format('d M Y') }} &bull; {{ $order->items->sum('qty') }} {{ __('items') }}</div>
                    </div>
                    <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ __('Total') }}</div>
                            <div class="font-bold text-gray-900">RM {{ number_format($order->total, 2) }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $order->status === 'completed' || $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            {{ __($order->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Logout Mobile -->
    <div class="px-4 md:hidden mt-6 mb-8">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-white border border-gray-200 text-red-500 font-bold py-3.5 rounded-xl shadow-sm active:scale-95 transition-transform">
                <i class="ph-bold ph-sign-out text-lg"></i> {{ __('Keluar Akun') }}
            </button>
        </form>
    </div>
</div>
