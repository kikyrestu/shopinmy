<div>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Loyalty & Referrals') }}</h1>
        </div>

        <!-- Points Banner -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-white dark:bg-gray-900/20 rounded-full blur-[80px]"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <p class="text-amber-100 font-medium mb-1">{{ __('Available Points') }}</p>
                    <h2 class="text-5xl font-black">{{ number_format($totalPoints) }} <span class="text-xl font-bold text-amber-200">pts</span></h2>
                    <p class="text-sm text-amber-50 mt-2">{{ __('Use points during checkout to get discounts.') }}</p>
                </div>
                <div class="w-16 h-16 bg-white dark:bg-gray-900/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                    <i class="ph-fill ph-star text-3xl text-amber-200"></i>
                </div>
            </div>
        </div>

        <!-- Referral Section -->
        @if($referralEnabled)
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="ph-bold ph-users text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Invite Friends, Earn Rewards!') }}</h3>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-1">{{ __('Share your referral code. When a friend signs up using your code, you both earn points!') }}</p>
                    
                    <div class="mt-6 flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider mb-2">{{ __('Your Referral Link') }}</label>
                            <div class="flex items-center bg-gray-50 dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 transition-all">
                                <input type="text" value="{{ $referralLink }}" readonly class="flex-1 bg-transparent border-none px-4 py-3 text-sm text-gray-600 dark:text-gray-400 outline-none w-full" id="refLink">
                                <button onclick="copyRefLink()" class="px-4 py-3 text-emerald-600 font-bold text-sm hover:bg-emerald-50 transition-colors border-l border-gray-200 dark:border-gray-700 flex items-center gap-2">
                                    <i class="ph-bold ph-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider mb-2">{{ __('Referral Code') }}</label>
                            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-center">
                                <span class="text-lg font-black text-amber-600 tracking-widest">{{ $user->referral_code }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Points History -->
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">{{ __('Points History') }}</h3>
            
            @if($user->loyaltyPoints->isEmpty())
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-[#121212] text-gray-400 dark:text-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ph-bold ph-clock text-2xl"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-500">{{ __('No point history available yet.') }}</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($user->loyaltyPoints as $point)
                        <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-gray-800 {{ $point->points > 0 ? 'bg-emerald-50/50' : 'bg-red-50/50' }}">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $point->points > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                    <i class="ph-bold {{ $point->points > 0 ? 'ph-arrow-down-left' : 'ph-arrow-up-right' }}"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $point->description ?: ucfirst($point->type) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ $point->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-black {{ $point->points > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $point->points > 0 ? '+' : '' }}{{ $point->points }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Referrals Made -->
        @if($referralEnabled)
        <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">{{ __('Your Referrals') }}</h3>
            
            @if($user->referralsMade->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-500">{{ __('You haven\'t invited anyone yet. Share your code to start earning!') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($user->referralsMade as $referral)
                        <div class="flex items-center gap-3 p-3 border border-gray-100 dark:border-gray-800 rounded-xl bg-gray-50 dark:bg-[#121212]">
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm">
                                {{ substr($referral->referee->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $referral->referee->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">Joined {{ $referral->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif
    </div>

    <script>
        function copyRefLink() {
            var copyText = document.getElementById("refLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            let btn = copyText.nextElementSibling;
            let originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph-bold ph-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }
    </script>
</div>
