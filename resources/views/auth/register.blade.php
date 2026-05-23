@extends('layouts.storefront')

@section('title', __('Register'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4 bg-gray-50">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-10">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="ph-bold ph-user-plus text-3xl"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ __('Create Account') }}</h1>
                <p class="text-gray-500 text-sm mt-2">{{ __('Join us and start your shopping experience.') }}</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Full Name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Email Address') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Referral Code (Optional) -->
                <div class="mb-6">
                    <label for="referral_code" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Referral Code (Optional)') }}</label>
                    <input id="referral_code" type="text" name="referral_code" value="{{ old('referral_code', request()->query('ref')) }}"
                        class="w-full bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 text-sm font-bold uppercase tracking-wider focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all placeholder:text-amber-300 placeholder:font-normal" placeholder="e.g. REF123">
                    @error('referral_code')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-brand-500/20 transform active:scale-[0.98] text-sm mb-4">
                    {{ __('Create Account') }}
                </button>
            </form>

            @if(\App\Models\Setting::get('google_login_enabled'))
            <div class="relative flex items-center justify-center mb-4">
                <span class="absolute bg-white px-2 text-xs text-gray-500 font-medium">{{ __('OR CONTINUE WITH') }}</span>
                <div class="w-full border-t border-gray-200"></div>
            </div>

            <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 py-3 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-bold rounded-xl transition-all shadow-sm transform active:scale-[0.98] text-sm">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google Logo" class="w-5 h-5">
                {{ __('Google') }}
            </a>
            @endif

            <!-- Login Link -->
            <div class="text-center mt-6 pt-6 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700 transition-colors">{{ __('Log In') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
