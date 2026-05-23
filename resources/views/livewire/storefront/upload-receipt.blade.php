<div class="mt-8">
    <div class="bg-white border-2 {{ $order->payment->status === 'failed' ? 'border-red-500' : 'border-brand-200' }} rounded-3xl p-6 sm:p-8 text-left shadow-lg shadow-brand-500/5 relative overflow-hidden">
        
        <!-- Background Pattern -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 relative z-10">
            <i class="ph-fill ph-wallet text-brand-500 text-2xl"></i> {{ __('Selesaikan Pembayaran Anda') }}
        </h2>

        @if (session('receipt-uploaded'))
            <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl text-sm font-medium border border-emerald-100 flex items-center gap-3 relative z-10">
                <i class="ph-bold ph-check-circle text-lg"></i>
                {{ session('receipt-uploaded') }}
            </div>
        @endif

        @if($order->payment->status === 'failed')
            <div class="mb-6 bg-red-50 text-red-700 p-5 rounded-2xl text-sm border border-red-200 relative z-10">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <i class="ph-fill ph-warning-circle text-lg"></i> {{ __('Bukti Transfer Ditolak') }}
                </div>
                <p class="mb-2">{{ __('Admin kami menolak bukti transfer yang Anda unggah sebelumnya dengan alasan:') }}</p>
                <div class="bg-white/60 p-3 rounded-lg border border-red-100 italic text-red-900 font-medium">
                    "{{ $order->payment->rejection_reason ?? 'Tidak ada alasan spesifik.' }}"
                </div>
                <p class="mt-3">{{ __('Silakan unggah ulang bukti transfer yang jelas dan benar.') }}</p>
            </div>
        @endif

        @if($order->payment->proof_image && $order->payment->status === 'pending')
            <!-- Waiting for verification -->
            <div class="bg-amber-50 border border-amber-100 p-6 rounded-2xl text-center relative z-10">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-amber-500">
                    <i class="ph-duotone ph-hourglass-high text-3xl animate-pulse"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">{{ __('Menunggu Verifikasi Admin') }}</h3>
                <p class="text-gray-600 text-sm mb-4">
                    {{ __('Bukti transfer Anda telah kami terima dan sedang dalam antrean pengecekan. Mohon tunggu maksimal 1x24 jam.') }}
                </p>
                <a href="{{ Storage::url($order->payment->proof_image) }}" target="_blank" class="inline-flex items-center gap-2 text-brand-600 font-semibold hover:text-brand-700 text-sm">
                    <i class="ph-bold ph-image"></i> {{ __('Lihat Bukti yang Diunggah') }}
                </a>
            </div>
        @else
            <!-- Transfer Instructions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                
                <!-- Left: Bank Details -->
                <div>
                    <p class="text-gray-600 text-sm mb-4">{{ __('Silakan transfer sejumlah nominal di bawah ini ke salah satu rekening kami:') }}</p>
                    
                    <div class="bg-brand-50 border border-brand-100 rounded-2xl p-5 mb-6 text-center">
                        <span class="block text-sm text-brand-600 font-bold uppercase tracking-wider mb-1">{{ __('Total Pembayaran') }}</span>
                        <span class="block text-3xl font-extrabold text-gray-900">RM {{ number_format($order->payment->amount, 2) }}</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($bankAccounts as $bank)
                            <div class="flex items-center gap-4 p-4 border border-gray-100 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                <div class="w-12 h-12 bg-white rounded-lg shadow-sm border border-gray-100 flex items-center justify-center p-2 flex-shrink-0">
                                    @if($bank->logo)
                                        <img src="{{ Storage::url($bank->logo) }}" alt="{{ $bank->bank_name }}" class="max-h-full max-w-full object-contain">
                                    @else
                                        <i class="ph-bold ph-bank text-gray-400 text-xl"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0" x-data="{ copied: false }">
                                    <div class="text-xs font-bold text-gray-500 uppercase">{{ $bank->bank_name }}</div>
                                    <div class="text-lg font-bold text-gray-900 mb-0.5 flex items-center gap-2">
                                        {{ $bank->account_number }}
                                        <button @click="navigator.clipboard.writeText('{{ $bank->account_number }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                                class="text-gray-400 hover:text-brand-600 transition-colors">
                                            <i class="ph-bold" :class="copied ? 'ph-check text-emerald-500' : 'ph-copy'"></i>
                                        </button>
                                    </div>
                                    <div class="text-sm text-gray-600 truncate">a.n. {{ $bank->account_name }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-500 text-sm italic">{{ __('Informasi rekening belum tersedia.') }}</div>
                        @endforelse
                    </div>
                </div>

                <!-- Right: Upload Form -->
                <div>
                    <form wire:submit="upload" class="h-full flex flex-col">
                        <label class="block text-sm font-bold text-gray-900 mb-3">{{ __('Upload Bukti Transfer') }} <span class="text-red-500">*</span></label>
                        
                        <div class="flex-1 relative group cursor-pointer min-h-[12rem] h-full border-2 border-dashed {{ $receiptImage ? 'border-brand-500 bg-brand-50/30' : 'border-gray-300 bg-gray-50 hover:bg-gray-100 hover:border-brand-400' }} rounded-2xl flex flex-col items-center justify-center transition-all overflow-hidden" 
                             onclick="document.getElementById('receipt-upload').click()">
                            
                            @if ($receiptImage)
                                <img src="{{ $receiptImage->temporaryUrl() }}" alt="Preview" class="absolute inset-0 w-full h-full object-cover opacity-60">
                                <div class="relative z-10 flex flex-col items-center text-brand-700 bg-white/80 px-4 py-2 rounded-xl backdrop-blur-sm">
                                    <i class="ph-fill ph-check-circle text-3xl mb-1 text-emerald-500"></i>
                                    <span class="font-bold text-sm">{{ __('Foto Dipilih') }}</span>
                                    <span class="text-xs">{{ __('Klik untuk mengganti') }}</span>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-brand-500 mb-3 shadow-sm group-hover:scale-110 transition-transform">
                                    <i class="ph-bold ph-upload-simple text-xl"></i>
                                </div>
                                <span class="font-bold text-gray-700 text-sm">{{ __('Pilih gambar atau foto') }}</span>
                                <span class="text-gray-400 text-xs mt-1">{{ __('PNG, JPG, max 2MB') }}</span>
                            @endif
                            
                            <input type="file" id="receipt-upload" wire:model="receiptImage" class="hidden" accept="image/*" required>
                        </div>
                        
                        @error('receiptImage') 
                            <span class="block mt-2 text-xs text-red-500 font-medium">{{ $message }}</span> 
                        @enderror

                        <button type="submit" class="mt-4 w-full py-3.5 bg-gray-900 hover:bg-black text-white font-bold rounded-xl transition-all shadow-lg transform active:scale-[0.98] text-sm flex items-center justify-center gap-2 disabled:opacity-70"
                                wire:loading.attr="disabled" wire:target="upload">
                            <span wire:loading.remove wire:target="upload">{{ __('Kirim Bukti Pembayaran') }}</span>
                            <span wire:loading wire:target="upload">
                                <i class="ph-bold ph-spinner animate-spin"></i> {{ __('Mengunggah...') }}
                            </span>
                        </button>
                    </form>
                </div>
                
            </div>
        @endif
    </div>
</div>
