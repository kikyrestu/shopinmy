<div class="max-w-4xl space-y-4 md:space-y-8 pb-20 md:pb-10">
    <!-- Header -->
    <div class="px-4 md:px-0">
        <h1 class="text-3xl font-extrabold text-gray-900">{{ __('My Profile') }}</h1>
        <p class="mt-2 text-sm text-gray-500">{{ __('Manage your personal information and security settings.') }}</p>
    </div>

    <!-- Personal Information Card -->
    <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 md:p-6 sm:p-8 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-xl font-bold text-gray-900">{{ __('Personal Information') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Update your photo and personal details here.') }}</p>
        </div>
        
        <div class="p-4 md:p-6 sm:p-8">
            <!-- Flash Message -->
            @if (session('profile-updated'))
                <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl text-sm font-medium border border-emerald-100 flex items-center gap-3">
                    <i class="ph-bold ph-check-circle text-lg"></i>
                    {{ session('profile-updated') }}
                </div>
            @endif

            <form wire:submit="updateProfile" class="space-y-8">
                <!-- Avatar Upload Section -->
                <div class="flex items-center gap-6">
                    <div class="relative group cursor-pointer" onclick="document.getElementById('avatar-upload').click()">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-24 h-24 rounded-2xl object-cover shadow-sm ring-4 ring-gray-50">
                        @else
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-24 h-24 rounded-2xl object-cover shadow-sm ring-4 ring-gray-50">
                        @endif
                        <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="ph-bold ph-camera text-white text-2xl"></i>
                        </div>
                        <input type="file" id="avatar-upload" wire:model="avatar" class="hidden" accept="image/*">
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ __('Profile Photo') }}</h3>
                        <p class="text-xs text-gray-500 mt-1 max-w-xs">{{ __('Click the image to change. Recommended size 500x500px, max 2MB.') }}</p>
                        <button type="button" onclick="document.getElementById('avatar-upload').click()" class="mt-3 text-sm font-semibold text-brand-600 hover:text-brand-700">
                            {{ __('Upload New Photo') }}
                        </button>
                        @error('avatar') <span class="block mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Full Name') }}</label>
                        <input id="name" type="text" wire:model="name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Phone Number') }}</label>
                        <input id="phone" type="text" wire:model="phone" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Email Address') }}</label>
                        @if($isGoogleUser)
                            <div class="relative">
                                <input id="email" type="email" value="{{ $email }}" disabled class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-xl px-4 py-3 text-sm outline-none cursor-not-allowed">
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="ph-fill ph-google-logo text-lg"></i>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                                <i class="ph-fill ph-info"></i> {{ __('Your email is securely managed by Google.') }}
                            </p>
                        @else
                            <input id="email" type="email" wire:model="email" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @endif
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-brand-500/20 transform active:scale-[0.98] text-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="updateProfile">{{ __('Save Changes') }}</span>
                        <span wire:loading wire:target="updateProfile">
                            <i class="ph-bold ph-spinner animate-spin"></i> {{ __('Saving...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Information Card (Only for Manual Users) -->
    @if(!$isGoogleUser)
    <div class="bg-white rounded-none md:rounded-3xl border-y md:border border-gray-100 shadow-sm overflow-hidden mt-4 md:mt-8">
        <div class="p-4 md:p-6 sm:p-8 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-xl font-bold text-gray-900">{{ __('Security & Password') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
        
        <div class="p-4 md:p-6 sm:p-8">
            @if (session('password-updated'))
                <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl text-sm font-medium border border-emerald-100 flex items-center gap-3">
                    <i class="ph-bold ph-shield-check text-lg"></i>
                    {{ session('password-updated') }}
                </div>
            @endif

            <form wire:submit="updatePassword" class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Current Password') }}</label>
                    <input id="current_password" type="password" wire:model="current_password" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('New Password') }}</label>
                    <input id="password" type="password" wire:model="password" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('password_confirmation') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="px-6 py-3 bg-gray-900 hover:bg-black text-white font-bold rounded-xl transition-all shadow-lg transform active:scale-[0.98] text-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="updatePassword">{{ __('Update Password') }}</span>
                        <span wire:loading wire:target="updatePassword">
                            <i class="ph-bold ph-spinner animate-spin"></i> {{ __('Updating...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
