@php
    $waPhone = \App\Models\Setting::get('store_phone'); // Typically store phone
    $waMessage = urlencode(__('Hello! I want to ask about...'));
@endphp

@if($waPhone)
    <div class="fixed bottom-6 right-6 z-50">
        <!-- Ping Animation -->
        <div class="absolute -inset-2 bg-[#25D366]/30 rounded-full animate-ping pointer-events-none"></div>
        
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $waPhone) }}?text={{ $waMessage }}" target="_blank" class="relative bg-[#25D366] hover:bg-[#128C7E] text-white w-14 h-14 rounded-full flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.12)] hover:-translate-y-1 transition-all group">
            <i class="ph-fill ph-whatsapp-logo text-3xl"></i>
            
            <!-- Tooltip -->
            <span class="absolute right-full mr-4 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                {{ __('Need Help? Chat us!') }}
                <div class="absolute right-[-4px] top-1/2 -translate-y-1/2 border-4 border-transparent border-l-gray-900"></div>
            </span>
        </a>
    </div>
@endif
