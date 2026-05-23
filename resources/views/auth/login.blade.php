@extends('layouts.storefront')

@section('title', __('Log In'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4 bg-gray-50">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-10">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-brand-100 text-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="ph-bold ph-user text-3xl"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900">{{ __('Welcome Back') }}</h1>
                <p class="text-gray-500 text-sm mt-2">{{ __('Log in to your account to continue shopping.') }}</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-sm text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Email Address') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between mb-6">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                        <span class="ml-2 text-sm text-gray-600 font-medium">{{ __('Remember me') }}</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-brand-500/20 transform active:scale-[0.98] text-sm mb-4">
                    {{ __('Log In') }}
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

            <!-- Register Link -->
            <div class="text-center mt-6 pt-6 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-700 transition-colors">{{ __('Register') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
