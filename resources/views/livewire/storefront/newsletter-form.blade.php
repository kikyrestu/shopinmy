<div>
    @if($successMessage)
        <div class="bg-emerald-50 text-emerald-600 rounded-xl px-4 py-3 text-sm font-semibold flex items-center gap-2">
            <i class="ph-bold ph-check-circle"></i> {{ __('Thank you for subscribing!') }}
        </div>
    @else
        <form wire:submit.prevent="subscribe" class="relative group mt-4">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="ph ph-envelope-simple text-xl text-gray-400 group-focus-within:text-brand-500 transition-colors"></i>
            </div>
            <input type="email" wire:model="email" required
                class="block w-full pl-12 pr-32 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all outline-none" 
                placeholder="{{ __('Enter your email address') }}">
            <div class="absolute inset-y-1.5 right-1.5 flex items-center">
                <button type="submit" class="px-5 py-2 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-sm">
                    <span wire:loading.remove wire:target="subscribe">{{ __('Subscribe') }}</span>
                    <span wire:loading wire:target="subscribe">{{ __('Wait...') }}</span>
                </button>
            </div>
        </form>
        @error('email')
            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
        @enderror
    @endif
</div>
