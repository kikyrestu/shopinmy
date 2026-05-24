<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <span class="text-lg font-bold">Produk Terlaris</span>
            </div>
        </x-slot>

        <div class="-mx-6 -mb-6">
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse($this->getTopProducts() as $product)
                    <li class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 dark:bg-[#121212] dark:hover:bg-white dark:bg-gray-900/5 transition-colors">
                        <div class="w-12 h-12 rounded-lg flex-shrink-0 flex items-center justify-center text-lg font-bold
                            {{ ['bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'][$loop->index % 5] }}">
                            {{ strtoupper(substr($product->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category_name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $product->total_sold }} Terjual</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">RM {{ number_format($product->total_revenue, 0) }}</p>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center">
                        <div class="text-gray-400 dark:text-gray-400">
                            <x-heroicon-o-shopping-bag class="w-8 h-8 mx-auto mb-2" />
                            <p class="text-sm">Belum ada data penjualan</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
