<div class="bg-gray-50 dark:bg-[#121212] min-h-screen pb-24">
    
    <!-- Top Header Sticky -->
    <div class="bg-white dark:bg-gray-900 sticky top-0 z-30 border-b border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.orders') }}" class="text-gray-900 dark:text-white">
                    <i class="ph-bold ph-arrow-left text-xl"></i>
                </a>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Detail Pesanan') }}</h1>
            </div>
            <div>
                <button class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                    <i class="ph-bold ph-question text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Status Banner Area -->
    <div class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 pt-4 pb-6 px-4 mb-2">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-extrabold text-gray-900 dark:text-white mb-1">
                    @if($order->status === 'pending')
                        Menunggu Pembayaran
                    @elseif($order->status === 'processing')
                        Pesanan Diproses
                    @elseif(in_array($order->status, ['shipped', 'delivered']))
                        Pesanan Dikirim
                    @elseif($order->status === 'completed')
                        Pesanan Selesai
                    @else
                        Pesanan Dibatalkan
                    @endif
                </h2>
                
                @if($order->status === 'pending')
                    <p class="text-sm text-gray-500 font-medium">Selesaikan pembayaran agar pesanan diproses.</p>
                @elseif($order->status === 'completed')
                    <p class="text-sm text-emerald-600 font-medium">Terima kasih telah berbelanja di toko kami!</p>
                @else
                    <p class="text-sm text-gray-500 font-medium">Pesanan kamu sedang dalam proses.</p>
                @endif
            </div>
            
            <div class="w-12 h-12 flex-shrink-0 bg-brand-50 rounded-full flex items-center justify-center text-brand-500">
                @if($order->status === 'pending')
                    <i class="ph-fill ph-wallet text-2xl"></i>
                @elseif($order->status === 'completed')
                    <i class="ph-fill ph-check-circle text-2xl text-emerald-500"></i>
                @elseif($order->status === 'cancelled')
                    <i class="ph-fill ph-x-circle text-2xl text-red-500"></i>
                @else
                    <i class="ph-fill ph-package text-2xl"></i>
                @endif
            </div>
        </div>

        @if($order->status === 'pending')
            <div class="bg-red-50 border border-red-100 rounded-xl p-3 flex justify-between items-center"
                 x-data="{
                     endTime: new Date('{{ $order->created_at->addHours(24)->toIso8601String() }}').getTime(),
                     timeLeft: '',
                     init() {
                         this.updateTimer();
                         setInterval(() => this.updateTimer(), 1000);
                     },
                     updateTimer() {
                         let now = new Date().getTime();
                         let distance = this.endTime - now;
                         if (distance < 0) {
                             this.timeLeft = '00:00:00';
                             return;
                         }
                         let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                         let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                         let seconds = Math.floor((distance % (1000 * 60)) / 1000);
                         this.timeLeft = hours.toString().padStart(2, '0') + ':' + 
                                         minutes.toString().padStart(2, '0') + ':' + 
                                         seconds.toString().padStart(2, '0');
                     }
                 }">
                <span class="text-xs font-bold text-red-700 uppercase">Sisa Waktu Bayar</span>
                <span class="text-sm font-extrabold text-red-700 font-mono" x-text="timeLeft"></span>
            </div>
        @endif
    </div>

    <!-- Info Kurir / Resi -->
    <div class="bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800 p-4 mb-2">
        <div class="flex items-center gap-3 mb-2">
            <i class="ph-fill ph-truck text-brand-500 text-xl"></i>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Info Pengiriman</h3>
        </div>
        <div class="ml-8">
            <p class="text-sm text-gray-900 dark:text-white font-medium uppercase">{{ $order->courier ?? 'Standard Shipping' }}</p>
            @if($order->tracking_no && !str_starts_with($order->tracking_no, 'ORD-'))
                <div class="flex items-center justify-between mt-1" x-data="{ copied: false }">
                    <p class="text-sm text-gray-500 font-mono">{{ $order->tracking_no }}</p>
                    <button @click="navigator.clipboard.writeText('{{ $order->tracking_no }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                            class="text-brand-600 text-xs font-bold flex items-center gap-1">
                        <span x-text="copied ? 'Tersalin' : 'SALIN'"></span>
                        <i class="ph-bold" :class="copied ? 'ph-check' : 'ph-copy'"></i>
                    </button>
                </div>
            @else
                <p class="text-sm text-gray-500 mt-1">Resi belum tersedia</p>
            @endif
        </div>
    </div>

    <!-- Detail Produk -->
    <div class="bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800 p-4 mb-2">
        <div class="flex items-center gap-2 mb-4">
            <i class="ph-fill ph-storefront text-gray-500 text-lg"></i>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Detail Produk</h3>
        </div>

        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex gap-3 items-start">
                    <!-- Image -->
                    <div class="w-16 h-16 bg-gray-50 dark:bg-[#121212] rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden flex-shrink-0">
                        @if($item->product->primaryImage)
                            <img src="{{ $item->product->first_image_url }}" class="w-full h-full object-cover">
                        @else
                            <i class="ph ph-image text-gray-300 w-full h-full flex items-center justify-center text-xl"></i>
                        @endif
                    </div>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate mb-1">{{ $item->product->name }}</h4>
                        @if($item->variant)
                            <p class="text-xs text-gray-500 mb-1">{{ $item->variant->name }}: {{ $item->variant->value }}</p>
                        @endif
                        <p class="text-xs text-gray-500">{{ $item->qty }} x RM {{ number_format($item->price, 2) }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pb-4 {{ !$loop->last ? 'border-b border-gray-50 dark:border-gray-800' : '' }}">
                    <p class="text-sm font-extrabold text-gray-900 dark:text-white mt-1">RM {{ number_format($item->price * $item->qty, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Info Pesanan & Pembayaran -->
    <div class="bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800 p-4 mb-2">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Rincian Pembayaran</h3>
        
        <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Total Harga ({{ $order->items->sum('qty') }} barang)</span>
                <span>RM {{ number_format($order->total - $order->shipping_cost - $order->tax_amount + ($order->voucher_id ? (\App\Models\Voucher::find($order->voucher_id)?->value ?? 0) : 0), 2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Total Ongkos Kirim</span>
                <span>RM {{ number_format($order->shipping_cost, 2) }}</span>
            </div>
            @if($order->tax_amount > 0)
                <div class="flex justify-between text-gray-500">
                    <span>Pajak (Tax)</span>
                    <span>RM {{ number_format($order->tax_amount, 2) }}</span>
                </div>
            @endif
            @if($order->voucher_id)
                <div class="flex justify-between text-emerald-600">
                    <span>Diskon Voucher</span>
                    <span>- RM {{ number_format(\App\Models\Voucher::find($order->voucher_id)?->value ?? 0, 2) }}</span>
                </div>
            @endif
        </div>
        
        <div class="my-4 border-t border-dashed border-gray-200 dark:border-gray-700"></div>
        
        <div class="flex justify-between items-center">
            <span class="text-sm font-bold text-gray-900 dark:text-white">Total Belanja</span>
            <span class="text-lg font-extrabold text-gray-900 dark:text-white">RM {{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800 p-4 mb-2" x-data="{ copied: false }">
        <div class="flex justify-between items-center mb-3">
            <span class="text-sm text-gray-500">No. Invoice</span>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-brand-600">{{ $order->order_number }}</span>
                <button @click="navigator.clipboard.writeText('{{ $order->order_number }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-brand-600 hover:text-brand-700">
                    <i class="ph-bold" :class="copied ? 'ph-check' : 'ph-copy'"></i>
                </button>
            </div>
        </div>
        <div class="flex justify-between items-center mb-3">
            <span class="text-sm text-gray-500">Tanggal Pembelian</span>
            <span class="text-sm text-gray-900 dark:text-white font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
        </div>
        <div class="flex justify-between items-start">
            <span class="text-sm text-gray-500">Metode Pembayaran</span>
            <span class="text-sm text-gray-900 dark:text-white font-medium capitalize text-right">
                {{ str_replace('_', ' ', $order->payment->method ?? 'N/A') }}
                @if(optional($order->payment)->status === 'paid')
                    <span class="block text-xs text-emerald-500 mt-0.5">Lunas</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Sticky Bottom Navigation Bar (Tokopedia Style) -->
    <div class="fixed left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 p-3 px-4 z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] md:bottom-0" style="bottom: calc(3.5rem + env(safe-area-inset-bottom));">
        <div class="max-w-lg mx-auto flex gap-3 items-center">
            
            @if($order->status === 'pending' && optional($order->payment)->method === 'manual_transfer')
                <div class="flex-1">
                    <p class="text-[10px] text-gray-500 uppercase font-bold">Total Tagihan</p>
                    <p class="text-sm font-extrabold text-brand-600">RM {{ number_format($order->total, 2) }}</p>
                </div>
                <a href="{{ route('checkout.success', $order->id) }}" class="flex-[1.5] py-2.5 bg-brand-500 text-white font-bold rounded-xl text-center text-sm active:scale-95 transition-transform shadow-sm shadow-brand-500/20">
                    Bayar Sekarang
                </a>
            @elseif($order->status === 'shipped')
                <button wire:click="completeOrder" wire:confirm="Yakin pesanan sudah diterima?" class="flex-[1.5] py-2.5 bg-brand-500 text-white font-bold rounded-xl text-center text-sm active:scale-95 transition-transform shadow-sm shadow-brand-500/20">
                    Pesanan Diterima
                </button>
            @elseif(in_array($order->status, ['completed', 'delivered']))
                @if($order->reviews->count() === 0)
                    <a href="{{ route('dashboard.orders.review', $order->id) }}" class="flex-[1.5] py-2.5 bg-brand-500 text-white font-bold rounded-xl text-center text-sm active:scale-95 transition-transform shadow-sm shadow-brand-500/20">
                        Beri Ulasan
                    </a>
                @else
                    <button class="flex-1 py-2.5 border border-brand-500 text-brand-600 font-bold rounded-xl text-center text-sm active:scale-95 transition-transform bg-brand-50 dark:bg-transparent">
                        Beli Lagi
                    </button>
                @endif
            @else
                <button class="flex-1 py-2.5 border border-brand-500 text-brand-600 font-bold rounded-xl text-center text-sm active:scale-95 transition-transform bg-brand-50 dark:bg-transparent">
                    Beli Lagi
                </button>
            @endif

        </div>
    </div>
</div>
