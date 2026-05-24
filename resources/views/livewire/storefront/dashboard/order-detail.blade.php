<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.orders') }}" class="w-10 h-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl flex items-center justify-center text-gray-500 dark:text-gray-500 hover:text-brand-600 hover:border-brand-200 hover:bg-brand-50 transition-all shadow-sm">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </a>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Order Details') }}</h1>
        </div>
        
        @if($order->status === 'pending')
            <button wire:click="cancelOrder" 
                    wire:confirm="{{ __('Are you sure you want to cancel this order? This action cannot be undone.') }}"
                    class="px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2 border border-red-100 hover:border-red-600">
                <i class="ph-bold ph-x"></i> {{ __('Batalkan Pesanan') }}
            </button>
        @endif
    </div>

    @if (session()->has('success'))
        <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl text-sm font-medium border border-emerald-100 flex items-center gap-3">
            <i class="ph-fill ph-check-circle text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl text-sm font-medium border border-red-100 flex items-center gap-3">
            <i class="ph-fill ph-warning-circle text-xl"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Items & Summary -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Order Items -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50 dark:bg-[#121212]/30">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="ph-fill ph-package text-brand-500"></i> {{ __('Items Ordered') }}
                    </h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ in_array($order->status, ['completed', 'delivered', 'shipped', 'paid']) ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                    ">
                        {{ __($order->status) }}
                    </span>
                </div>
                
                <div class="divide-y divide-gray-50">
                    @foreach($order->items as $item)
                        <div class="p-6 flex flex-col sm:flex-row gap-5 items-start sm:items-center">
                            <div class="w-20 h-20 bg-gray-50 dark:bg-[#121212] rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex-shrink-0">
                                @if($item->product->primaryImage)
                                    <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                                @else
                                    <i class="ph ph-image text-gray-300 w-full h-full flex items-center justify-center text-2xl"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-bold text-gray-900 dark:text-gray-100 truncate mb-1">{{ $item->product->name }}</h4>
                                <div class="text-sm text-gray-500 dark:text-gray-500 flex flex-wrap items-center gap-x-4 gap-y-2">
                                    <span class="flex items-center gap-1"><i class="ph-bold ph-x"></i> {{ $item->qty }}</span>
                                    @if($item->variant)
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded-md text-xs">{{ $item->variant->name }}: {{ $item->variant->value }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right w-full sm:w-auto mt-2 sm:mt-0">
                                <div class="text-sm text-gray-500 dark:text-gray-500 mb-0.5">RM {{ number_format($item->price, 2) }} / {{ __('item') }}</div>
                                <div class="text-lg font-extrabold text-gray-900 dark:text-gray-100">RM {{ number_format($item->price * $item->qty, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pricing Summary -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 sm:p-8">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                    <i class="ph-fill ph-receipt text-brand-500"></i> {{ __('Payment Summary') }}
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">RM {{ number_format($order->total - $order->shipping_cost - $order->tax_amount + ($order->voucher_id ? (\App\Models\Voucher::find($order->voucher_id)?->value ?? 0) : 0), 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span>{{ __('Shipping Cost') }}</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">RM {{ number_format($order->shipping_cost, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span>{{ __('Tax') }}</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">RM {{ number_format($order->tax_amount, 2) }}</span>
                    </div>

                    @if($order->voucher_id)
                        <div class="flex justify-between items-center text-emerald-600">
                            <span>{{ __('Discount (Voucher)') }}</span>
                            <span class="font-bold">- RM {{ number_format(\App\Models\Voucher::find($order->voucher_id)?->value ?? 0, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Grand Total') }}</span>
                        <span class="text-2xl font-extrabold text-brand-600">RM {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Shipping & Payment Info -->
        <div class="space-y-6">
            
            <!-- Tracking Info -->
            @if($order->tracking_no && !str_starts_with($order->tracking_no, 'ORD-'))
                <div class="bg-brand-50 border border-brand-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 text-brand-500/10">
                        <i class="ph-fill ph-truck text-9xl"></i>
                    </div>
                    <h2 class="text-sm font-bold text-brand-800 uppercase tracking-wider mb-2 relative z-10">{{ __('Tracking Information') }}</h2>
                    <div class="font-bold text-gray-900 dark:text-gray-100 text-xl mb-4 relative z-10">{{ $order->tracking_no }}</div>
                    <a href="{{ route('dashboard.orders.track', $order->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-sm relative z-10 text-sm">
                        <i class="ph-bold ph-map-pin"></i> {{ __('Track Package') }}
                    </a>
                </div>
            @endif

            <!-- General Info -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-sm font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider mb-4">{{ __('Order Info') }}</h2>
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">{{ __('Order Number') }}</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100">{{ $order->order_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">{{ __('Order Date') }}</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100">{{ $order->created_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Shipping Details -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-sm font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider mb-4">{{ __('Shipping Details') }}</h2>
                
                <div class="mb-4">
                    <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">{{ __('Courier') }}</div>
                    <div class="font-bold text-gray-900 dark:text-gray-100 uppercase">
                        {{ $order->courier ? strtoupper($order->courier) : __('Standard Shipping') }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">{{ __('Shipping Address') }}</div>
                    <div class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed font-medium">
                        {{ $order->guest_name }}<br>
                        {{ $order->guest_phone }}<br>
                        {{ $order->guest_address }}<br>
                        {{ $order->guest_city }}, {{ $order->guest_state }} {{ $order->guest_postcode }}
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-sm font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider mb-4">{{ __('Payment Details') }}</h2>
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">{{ __('Method') }}</div>
                        <div class="font-bold text-gray-900 dark:text-gray-100 capitalize flex items-center gap-2">
                            <i class="ph-fill ph-credit-card text-brand-500 text-lg"></i> 
                            {{ str_replace('_', ' ', $order->payment->method ?? 'N/A') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mb-1">{{ __('Payment Status') }}</div>
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider
                            {{ optional($order->payment)->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ optional($order->payment)->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ optional($order->payment)->status === 'failed' || optional($order->payment)->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            {{ __(optional($order->payment)->status ?? 'Unknown') }}
                        </span>
                    </div>
                </div>
                
                @if(optional($order->payment)->method === 'manual_transfer' && optional($order->payment)->status === 'pending' && $order->status !== 'cancelled')
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('checkout.success', $order->id) }}" class="block w-full py-2.5 bg-brand-50 text-brand-600 hover:bg-brand-100 font-bold rounded-xl text-center transition-colors text-sm">
                            {{ __('Upload Bukti Transfer') }}
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
