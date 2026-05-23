<div x-data="{ open: false }" class="relative hidden lg:block">
    <!-- Bell Button -->
    <button @click="open = !open" @click.away="open = false" class="relative p-2.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-all">
        <i class="ph ph-bell text-2xl"></i>
        @if($this->unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 rounded-full ring-2 ring-white text-[10px] text-white font-bold flex items-center justify-center animate-pulse">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl shadow-brand-500/10 border border-gray-100 z-50 overflow-hidden"
         style="display: none;">
        
        <div class="p-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-bold text-gray-900">{{ __('Notifications') }}</h3>
            @if($this->unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors">
                    {{ __('Mark all read') }}
                </button>
            @endif
        </div>

        <div class="max-h-[360px] overflow-y-auto overscroll-contain">
            @if($this->notifications->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-300">
                        <i class="ph-fill ph-bell-slash text-2xl"></i>
                    </div>
                    <p class="text-sm">{{ __('No notifications yet.') }}</p>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($this->notifications as $notification)
                        <button wire:click="markAsRead('{{ $notification->id }}')" 
                                class="w-full text-left p-4 hover:bg-gray-50 transition-colors flex gap-4 {{ is_null($notification->read_at) ? 'bg-brand-50/30' : '' }}">
                            <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center mt-1 
                                {{ is_null($notification->read_at) ? 'bg-brand-100 text-brand-600' : 'bg-gray-100 text-gray-400' }}">
                                @if(isset($notification->data['icon']))
                                    <i class="ph-bold {{ $notification->data['icon'] }} text-lg"></i>
                                @else
                                    <i class="ph-bold ph-bell-ringing text-lg"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm {{ is_null($notification->read_at) ? 'font-bold text-gray-900' : 'font-medium text-gray-700' }} mb-0.5">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </p>
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-1.5">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <span class="text-[10px] font-bold uppercase tracking-wider {{ is_null($notification->read_at) ? 'text-brand-500' : 'text-gray-400' }}">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if(is_null($notification->read_at))
                                <div class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0 mt-2"></div>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- View All link placeholder if needed -->
        <div class="p-3 border-t border-gray-50 text-center bg-gray-50/50">
            <p class="text-xs text-gray-400">{{ __('Showing latest notifications') }}</p>
        </div>
    </div>
</div>
