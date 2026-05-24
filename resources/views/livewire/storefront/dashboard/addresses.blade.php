<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Address Book') }}</h1>
        <button wire:click="$set('isEditing', true)" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
            <i class="ph-bold ph-plus"></i> {{ __('Add New Address') }}
        </button>
    </div>

    @if($isEditing)
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 sm:p-8 mb-8 animate-fade-in-up">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">{{ $addressId ? __('Edit Address') : __('Add New Address') }}</h2>
            <form wire:submit.prevent="saveAddress">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Label (e.g. Home, Office)') }}</label>
                        <input type="text" wire:model="label" class="w-full bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('label') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Street Address') }} *</label>
                        <input type="text" wire:model="address" required class="w-full bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('City') }} *</label>
                        <input type="text" wire:model="city" required class="w-full bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('city') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('State') }} *</label>
                        <input type="text" wire:model="state" required class="w-full bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('state') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Postcode') }} *</label>
                        <input type="text" wire:model="postcode" required class="w-full bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:bg-white dark:bg-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('postcode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2 mt-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_default" class="w-5 h-5 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Set as Default Address') }}</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="resetForm" class="px-6 py-2.5 text-gray-600 dark:text-gray-400 font-bold hover:bg-gray-100 dark:bg-gray-800 rounded-xl transition-colors">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-sm transition-colors">{{ __('Save Address') }}</button>
                </div>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($addresses as $addr)
        <div class="bg-white dark:bg-gray-900 rounded-3xl border {{ $addr->is_default ? 'border-brand-500 ring-1 ring-brand-500' : 'border-gray-100 dark:border-gray-800' }} shadow-sm p-6 relative">
            @if($addr->is_default)
                <span class="absolute top-0 right-0 bg-brand-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl rounded-tr-3xl uppercase tracking-wider">{{ __('Default') }}</span>
            @endif
            
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center">
                        <i class="ph-fill {{ strtolower($addr->label) == 'office' ? 'ph-buildings' : 'ph-house' }} text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">{{ $addr->label ?? __('Address') }}</h3>
                </div>
                <div class="flex items-center gap-1">
                    <button wire:click="editAddress({{ $addr->id }})" class="w-8 h-8 flex items-center justify-center text-gray-400 dark:text-gray-600 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-colors" title="{{ __('Edit') }}">
                        <i class="ph ph-pencil-simple text-lg"></i>
                    </button>
                    <button wire:click="deleteAddress({{ $addr->id }})" wire:confirm="{{ __('Are you sure you want to delete this address?') }}" class="w-8 h-8 flex items-center justify-center text-gray-400 dark:text-gray-600 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors" title="{{ __('Delete') }}">
                        <i class="ph ph-trash text-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ $addr->address }}<br>
                {{ $addr->city }}, {{ $addr->postcode }}<br>
                {{ $addr->state }}
            </div>
        </div>
        @empty
            <div class="col-span-full p-12 text-center bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800">
                <div class="w-20 h-20 bg-gray-50 dark:bg-[#121212] rounded-full flex items-center justify-center text-gray-300 mx-auto mb-4">
                    <i class="ph ph-map-pin text-4xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">{{ __('No addresses saved') }}</h3>
                <p class="text-gray-500 dark:text-gray-500">{{ __('Add a shipping address to make checkout faster.') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Notification Toast (Livewire) -->
    <div x-data="{ show: false, message: '' }" 
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => { show = false }, 3000)"
         class="fixed bottom-5 right-5 z-50">
        <div x-show="show" x-transition.opacity.duration.300ms class="bg-gray-900 text-white px-6 py-3 rounded-xl shadow-xl font-medium flex items-center gap-3">
            <i class="ph-fill ph-check-circle text-emerald-400 text-xl"></i>
            <span x-text="message"></span>
        </div>
    </div>
</div>
