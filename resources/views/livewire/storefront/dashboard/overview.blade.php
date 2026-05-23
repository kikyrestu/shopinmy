<div>
    <h1 class="text-2xl font-extrabold text-gray-900 mb-6">{{ __('Account Overview') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center">
                    <i class="ph-fill ph-package text-2xl"></i>
                </div>
                <div>
                    <div class="text-gray-500 font-medium text-sm">{{ __('Total Orders') }}</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $totalOrders }}</div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center">
                    <i class="ph-fill ph-money text-2xl"></i>
                </div>
                <div>
                    <div class="text-gray-500 font-medium text-sm">{{ __('Total Spent') }}</div>
                    <div class="text-2xl font-extrabold text-gray-900">RM {{ number_format($totalSpent, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-10 pointer-events-none">
                <i class="ph-fill ph-star text-9xl text-amber-500"></i>
            </div>
            <div class="flex items-center gap-4 mb-4 relative z-10">
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center">
                    <i class="ph-fill ph-star text-2xl"></i>
                </div>
                <div>
                    <div class="text-gray-500 font-medium text-sm">{{ __('Loyalty Points') }}</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ auth()->user()->loyaltyPoints()->sum('points') ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
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
</div>
