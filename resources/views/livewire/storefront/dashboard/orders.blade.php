<div class="bg-gray-50 dark:bg-[#121212] min-h-screen pb-20">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 sticky top-0 z-30 border-b border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ __('Daftar Pesanan') }}</h1>
            <a href="{{ route('dashboard') }}" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-brand-600">
                <i class="ph-bold ph-x text-xl"></i>
            </a>
        </div>
        
        <!-- Sliding Tabs (Pill Style) -->
        <div class="px-4 py-3 overflow-x-auto no-scrollbar flex items-center gap-2">
            <button wire:click="$set('activeTab', 'all')" 
                    class="whitespace-nowrap px-4 py-1.5 font-bold rounded-full text-sm border transition-colors {{ $activeTab === 'all' ? 'bg-brand-500 text-white border-brand-500 shadow-sm shadow-brand-500/20' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">Semua</button>
            <button wire:click="$set('activeTab', 'ongoing')" 
                    class="whitespace-nowrap px-4 py-1.5 font-bold rounded-full text-sm border transition-colors {{ $activeTab === 'ongoing' ? 'bg-brand-500 text-white border-brand-500 shadow-sm shadow-brand-500/20' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">Berlangsung</button>
            <button wire:click="$set('activeTab', 'completed')" 
                    class="whitespace-nowrap px-4 py-1.5 font-bold rounded-full text-sm border transition-colors {{ $activeTab === 'completed' ? 'bg-brand-500 text-white border-brand-500 shadow-sm shadow-brand-500/20' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">Selesai</button>
            <button wire:click="$set('activeTab', 'cancelled')" 
                    class="whitespace-nowrap px-4 py-1.5 font-bold rounded-full text-sm border transition-colors {{ $activeTab === 'cancelled' ? 'bg-brand-500 text-white border-brand-500 shadow-sm shadow-brand-500/20' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">Dibatalkan</button>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-0 sm:px-4 mt-4 sm:mt-6">
        @if($orders->isEmpty())
            <div class="p-16 text-center bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 mx-4 sm:mx-0 shadow-sm">
                <div class="w-24 h-24 bg-gray-50 dark:bg-[#121212] rounded-full flex items-center justify-center text-gray-300 mx-auto mb-6">
                    <i class="ph ph-package text-5xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('Belum ada pesanan') }}</h3>
                <p class="text-gray-500 dark:text-gray-500 mb-6 max-w-sm mx-auto text-sm">{{ __('Mulai belanja dan nikmati berbagai promo menarik dari kami.') }}</p>
                <a href="{{ route('products.index') }}" class="inline-block px-8 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-lg shadow-brand-500/30 text-sm">{{ __('Mulai Belanja') }}</a>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach($orders as $order)
                <!-- Tokopedia Style Order Card -->
                <div class="bg-white dark:bg-gray-900 mx-4 sm:mx-0 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-visible">
                    
                    <!-- Card Header -->
                    <div class="px-4 py-3 flex items-center justify-between border-b border-gray-50 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <i class="ph-fill ph-storefront text-gray-400 text-lg"></i>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Belanja &bull; {{ $order->created_at->format('d M Y') }}</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wide
                            {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ in_array($order->status, ['completed', 'delivered', 'shipped', 'paid']) ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            {{ __($order->status) }}
                        </span>
                    </div>

                    <!-- Card Body -->
                    <a href="{{ route('dashboard.orders.show', $order->id) }}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-[#121212]/50 transition-colors">
                        @php $firstItem = $order->items->first(); @endphp
                        @if($firstItem)
                            <div class="flex gap-3">
                                <!-- Product Image -->
                                <div class="w-16 h-16 bg-gray-50 dark:bg-[#121212] rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden flex-shrink-0">
                                    @if($firstItem->product->primaryImage)
                                        <img src="{{ $firstItem->product->first_image_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="ph ph-image text-gray-300 w-full h-full flex items-center justify-center text-xl"></i>
                                    @endif
                                </div>
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $firstItem->product->name }}</h4>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $firstItem->qty }} barang
                                        @if($order->items->count() > 1)
                                            &bull; +{{ $order->items->count() - 1 }} produk lainnya
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </a>

                    <!-- Card Footer -->
                    <div class="px-4 py-3 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] text-gray-500 font-medium">{{ __('Total Belanja') }}</p>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-white">RM {{ number_format($order->total, 2) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Action Buttons Based on Status -->
                            @if($order->status === 'pending' && optional($order->payment)->method === 'manual_transfer')
                                <a href="{{ route('checkout.success', $order->id) }}" class="px-4 py-1.5 bg-brand-500 text-white text-xs font-bold rounded-full shadow-sm shadow-brand-500/20 active:scale-95 transition-transform">
                                    Bayar Sekarang
                                </a>
                            @elseif($order->tracking_no && !str_starts_with($order->tracking_no, 'ORD-'))
                                <a href="{{ route('dashboard.orders.track', $order->id) }}" class="px-4 py-1.5 border border-brand-500 text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-500/10 text-xs font-bold rounded-full active:scale-95 transition-transform">
                                    Lacak
                                </a>
                            @else
                                <a href="{{ route('dashboard.orders.show', $order->id) }}" class="px-4 py-1.5 bg-brand-500 text-white text-xs font-bold rounded-full shadow-sm shadow-brand-500/20 active:scale-95 transition-transform">
                                    Beli Lagi
                                </a>
                            @endif
                            
                            <!-- 3 Dots Dropdown Menu -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" @click.away="open = false" class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <i class="ph-bold ph-dots-three text-lg"></i>
                                </button>
                                
                                <div x-show="open" style="display: none;" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute bottom-full right-0 mb-2 w-48 bg-white dark:bg-gray-900 rounded-xl shadow-xl border border-gray-100 dark:border-gray-800 z-50 overflow-hidden">
                                    <a href="{{ route('dashboard.orders.show', $order->id) }}" class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium border-b border-gray-50 dark:border-gray-800 last:border-0 flex items-center gap-2">
                                        <i class="ph-bold ph-file-text"></i> Lihat Detail Pesanan
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::get('whatsapp_number', '60123456789')) }}?text=Halo admin, saya mau tanya tentang pesanan {{ $order->order_number }}" target="_blank" class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium border-b border-gray-50 dark:border-gray-800 last:border-0 flex items-center gap-2">
                                        <i class="ph-bold ph-whatsapp-logo"></i> Tanya Penjual
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($orders->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $orders->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
