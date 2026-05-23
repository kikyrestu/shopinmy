<div>
    <h1 class="text-2xl font-extrabold text-gray-900 mb-6">{{ __('My Orders') }}</h1>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-16 text-center">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-6">
                    <i class="ph ph-package text-5xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('No orders found') }}</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">{{ __('You have not placed any orders yet. Once you do, you can track their status here.') }}</p>
                <a href="{{ route('products.index') }}" class="inline-block px-8 py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-full transition-all shadow-lg shadow-brand-500/30 transform hover:-translate-y-0.5">{{ __('Start Shopping') }}</a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($orders as $order)
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center mb-6 pb-6 border-b border-gray-50">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ $order->order_number }}</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'completed' || $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                ">
                                    {{ __($order->status) }}
                                </span>
                                <a href="{{ route('dashboard.orders.show', $order->id) }}" class="ml-2 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold uppercase hover:bg-gray-200 transition-colors">
                                    <i class="ph-bold ph-eye mr-1"></i> Detail
                                </a>
                                @if($order->tracking_no && !str_starts_with($order->tracking_no, 'ORD-'))
                                    <a href="{{ route('dashboard.orders.track', $order->id) }}" class="ml-1 px-3 py-1 bg-brand-50 text-brand-600 rounded-full text-xs font-bold uppercase hover:bg-brand-100 transition-colors">
                                        <i class="ph-bold ph-map-pin mr-1"></i> Track
                                    </a>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">{{ $order->created_at->format('d M Y, H:i') }} &bull; {{ $order->items->sum('qty') }} {{ __('items') }}</div>
                        </div>
                        <div class="text-left sm:text-right w-full sm:w-auto">
                            <div class="text-sm text-gray-500 mb-1">{{ __('Total Amount') }}</div>
                            <div class="text-xl font-extrabold text-brand-600">RM {{ number_format($order->total, 2) }}</div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="flex gap-4 items-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-xl border border-gray-100 overflow-hidden flex-shrink-0">
                                @if($item->product->primaryImage)
                                    <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                                @else
                                    <i class="ph ph-image text-gray-300 w-full h-full flex items-center justify-center text-xl"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ $item->product->name }}</h4>
                                <div class="text-sm text-gray-500 mt-0.5">
                                    {{ __('Qty') }}: {{ $item->qty }} 
                                    @if($item->variant)
                                        | {{ $item->variant->name }}: {{ $item->variant->value }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm font-bold text-gray-900 whitespace-nowrap">
                                RM {{ number_format($item->price * $item->qty, 2) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($orders->hasPages())
            <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                {{ $orders->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
