<div>
    @section('title', __('Track Package'))

    <div class="bg-gray-50 dark:bg-[#121212] py-6 border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex text-sm text-gray-500 dark:text-gray-500 font-medium">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition-colors">{{ __('Dashboard') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('dashboard.orders') }}" class="hover:text-brand-600 transition-colors">{{ __('My Orders') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900 dark:text-gray-100">{{ __('Track Package') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('dashboard.orders') }}" class="w-10 h-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-500 hover:text-brand-600 hover:border-brand-500 transition-all shadow-sm flex-shrink-0">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Track Package') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-500">{{ __('Order Number') }}: <span class="font-bold text-gray-900 dark:text-gray-100">{{ $order->order_number }}</span></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 sm:p-8">
            @if($isLoading)
                <div class="py-12 text-center text-gray-500 dark:text-gray-500">
                    <i class="ph-bold ph-spinner animate-spin text-3xl text-brand-500 mb-4 inline-block"></i>
                    <p>{{ __('Loading tracking information...') }}</p>
                </div>
            @elseif($error === 'waiting_update')
                <div class="bg-blue-50/50 border border-blue-100 p-8 rounded-3xl text-center max-w-xl mx-auto mt-4 mb-4">
                    <div class="w-20 h-20 bg-white dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm ring-4 ring-blue-50">
                        <i class="ph-duotone ph-truck text-4xl text-blue-500 animate-pulse"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('Menunggu Update Kurir') }}</h3>
                    <p class="text-gray-500 dark:text-gray-500 text-sm leading-relaxed mb-8">
                        {{ __('Paket Anda sudah terdaftar di sistem. Kurir mungkin membutuhkan waktu 1x24 jam untuk memperbarui status pelacakan paket. Silakan cek kembali nanti.') }}
                    </p>
                    
                    @if($order->tracking_no && !str_starts_with($order->tracking_no, 'ORD-'))
                        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-left">
                            <div>
                                <span class="text-xs font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider">{{ __('Kurir') }}</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-gray-100 uppercase mt-0.5">{{ $order->courier ?? 'Unknown' }}</span>
                            </div>
                            <div class="w-px h-10 bg-gray-100 dark:bg-gray-800 hidden sm:block"></div>
                            <div class="flex-1 text-center sm:text-left">
                                <span class="text-xs font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider">{{ __('Nomor Resi') }}</span>
                                <span class="block text-lg font-extrabold text-brand-600 mt-0.5 tracking-wide">{{ $order->tracking_no }}</span>
                            </div>
                            <div x-data="{ copied: false }">
                                <button @click="navigator.clipboard.writeText('{{ $order->tracking_no }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                        class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-[#121212] hover:bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-xl transition-colors text-sm font-semibold">
                                    <i class="ph-bold" :class="copied ? 'ph-check text-emerald-500' : 'ph-copy'"></i>
                                    <span x-text="copied ? '{{ __('Tersalin!') }}' : '{{ __('Copy Resi') }}'"></span>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    <button wire:click="refreshTracking" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-brand-500/20 transform active:scale-[0.98] text-sm">
                        <i class="ph-bold ph-arrows-clockwise" wire:loading.class="animate-spin" wire:target="refreshTracking"></i>
                        {{ __('Cek Ulang Status') }}
                    </button>
                </div>
            @elseif($error)
                <div class="bg-red-50 text-red-600 p-6 rounded-2xl text-center">
                    <i class="ph-fill ph-warning-circle text-3xl mb-2"></i>
                    <p class="font-medium">{{ $error }}</p>
                </div>
            @else
                <div class="flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center p-6 bg-gray-50 dark:bg-[#121212] rounded-2xl mb-8">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-500 mb-1">{{ __('Tracking Number') }}</div>
                        <div class="text-xl font-extrabold text-gray-900 dark:text-gray-100">{{ $order->tracking_no }}</div>
                    </div>
                    <div class="text-left sm:text-right">
                        <div class="text-sm text-gray-500 dark:text-gray-500 mb-1">{{ __('Courier') }}</div>
                        <div class="text-lg font-bold text-brand-600 uppercase">{{ $order->courier }}</div>
                    </div>
                </div>

                <div class="relative pl-6 sm:pl-8 border-l-2 border-gray-100 dark:border-gray-800 ml-4 sm:ml-6 space-y-8">
                    @foreach($trackingData as $index => $event)
                        <div class="relative">
                            <div class="absolute -left-[35px] sm:-left-[43px] w-6 h-6 sm:w-8 sm:h-8 rounded-full border-4 {{ $index === 0 ? 'bg-brand-500 border-white shadow-md' : 'bg-gray-200 border-white' }} flex items-center justify-center">
                                @if($index === 0)
                                    <div class="w-2 h-2 bg-white dark:bg-gray-900 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-bold {{ $index === 0 ? 'text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }}">{{ $event['status'] ?? 'Updated' }}</div>
                                <div class="text-sm {{ $index === 0 ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-500' }} mt-1">{{ $event['description'] ?? '' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-600 mt-2 font-medium flex items-center gap-1.5">
                                    <i class="ph-bold ph-clock"></i>
                                    {{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->format('d M Y, H:i') : '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</div>
