@if($payment && $payment->proof_image)
    <div class="flex flex-col items-center gap-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800">
        <div class="flex justify-between w-full mb-2">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Tagihan:</p>
                <p class="text-lg font-bold text-brand-600 dark:text-brand-400">RM {{ number_format($payment->amount, 2) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500 dark:text-gray-400">Metode:</p>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">Transfer Manual</p>
            </div>
        </div>
        <img src="/storage/{{ $payment->proof_image }}" alt="Bukti Transfer" class="max-h-[60vh] w-auto object-contain rounded-lg shadow-lg">
    </div>
@else
    <div class="p-8 text-center bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
        <x-heroicon-o-photo class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <p class="text-gray-500 dark:text-gray-400 font-medium">Gambar tidak ditemukan atau belum diupload.</p>
    </div>
@endif
