<?php

namespace App\Livewire\Storefront;

use App\Models\FlashSale;
use Livewire\Component;

class FlashSaleView extends Component
{
    public $activeFlashSale;

    public function mount()
    {
        $this->activeFlashSale = FlashSale::with(['products' => function ($q) {
                $q->with('primaryImage', 'productImages', 'reviews');
            }])
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    public function render()
    {
        return view('livewire.storefront.flash-sale-view')
            ->extends('layouts.storefront')
            ->section('content');
    }
}
