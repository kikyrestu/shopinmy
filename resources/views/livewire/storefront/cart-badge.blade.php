@if($minimal)
    <span class="absolute -top-1 -right-1 w-4 h-4 bg-brand-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border border-white">
        {{ $count > 99 ? '99+' : $count }}
    </span>
@else
    <a href="{{ route('cart.index') }}" class="p-2 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-all relative group">
        <i class="ph ph-shopping-cart-simple text-2xl"></i>
        <span class="absolute top-0 right-0 w-4 h-4 bg-brand-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white group-hover:border-brand-50 transition-colors">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    </a>
@endif
