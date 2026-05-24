<div class="py-12 bg-gray-50 dark:bg-[#121212] min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="ph-fill ph-shield-check text-brand-500"></i>
                    Verifikasi Pembayaran Manual
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Halaman khusus Admin super cepat & aman untuk bypass *bug* symlink server.
                </p>
            </div>
            <div>
                <a href="/admin" class="bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-xl font-bold transition-colors text-sm">
                    Kembali ke Filament
                </a>
            </div>
        </div>

        @if($payments->isEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="w-24 h-24 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="ph-duotone ph-check-circle text-5xl text-emerald-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Hore! Antrean Kosong</h3>
                <p class="text-gray-500 dark:text-gray-400">Semua bukti transfer manual sudah diverifikasi.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($payments as $payment)
                    <div class="bg-white dark:bg-gray-900 rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow flex flex-col">
                        
                        <!-- Image Viewer Bypass Symlink -->
                        <div class="relative bg-gray-100 dark:bg-gray-800 group cursor-pointer h-64"
                             x-data="{ openPreview: false }">
                            
                            @if($payment->proof_image)
                                <!-- DIRECT ROUTE BYPASS NGINX -->
                                <img src="/storage/{{ $payment->proof_image }}" alt="Bukti Transfer" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                     @click="openPreview = true">
                                    <span class="bg-white/20 backdrop-blur-md text-white px-4 py-2 rounded-full font-bold text-sm flex items-center gap-2">
                                        <i class="ph-bold ph-arrows-out"></i> Lihat Full
                                    </span>
                                </div>

                                <!-- Fullscreen Modal (Alpine) -->
                                <div x-show="openPreview" style="display: none;" class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center p-4 backdrop-blur-sm"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95">
                                    <img src="/storage/{{ $payment->proof_image }}" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl">
                                    <button @click="openPreview = false" class="absolute top-6 right-6 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors">
                                        <i class="ph-bold ph-x text-2xl"></i>
                                    </button>
                                </div>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                    <i class="ph-duotone ph-image-broken text-4xl mb-2"></i>
                                    <span class="text-sm font-medium">Tidak ada foto</span>
                                </div>
                            @endif
                        </div>

                        <!-- Info Detail -->
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="inline-block px-3 py-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold rounded-full mb-2">
                                        MENUNGGU VERIFIKASI
                                    </span>
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Order #{{ $payment->order->id }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Oleh: {{ $payment->order->user->name ?? 'Customer' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Bayar</p>
                                    <p class="font-black text-brand-600 dark:text-brand-400">RM {{ number_format($payment->amount, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-3">
                                <button wire:click="openRejectModal({{ $payment->id }})" class="w-full py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                                    <i class="ph-bold ph-x"></i> Tolak
                                </button>
                                <button wire:click="approve({{ $payment->id }})" wire:confirm="Yakin ingin menyetujui pembayaran ini?" class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                                    <i class="ph-bold ph-check"></i> Terima
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Penolakan -->
    <x-modal name="reject-payment-modal" :show="false" focusable>
        <form wire:submit="reject" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="ph-fill ph-warning-circle text-red-500 text-xl"></i> Alasan Penolakan
            </h2>
            
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Beritahu customer alasan mengapa bukti transfer ini ditolak (misal: nominal kurang, foto buram, atau palsu).
            </p>

            <div class="mb-6">
                <textarea wire:model="rejectionReason" rows="3" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 rounded-xl shadow-sm" placeholder="Contoh: Maaf, foto buram tidak bisa dibaca. Mohon foto ulang dengan jelas."></textarea>
                @error('rejectionReason') 
                    <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> 
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-lg shadow-lg shadow-red-500/20 transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="reject">Tolak Pembayaran</span>
                    <span wire:loading wire:target="reject"><i class="ph-bold ph-spinner animate-spin"></i> Memproses...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
