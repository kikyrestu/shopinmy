<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;

class OrderHistory extends Component
{
    use WithPagination;

    public $activeTab = 'all';

    public function render()
    {
        $query = Order::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere(function($q2) {
                      $q2->whereNull('user_id')
                         ->where('guest_email', auth()->user()->email);
                  });
            });

        if ($this->activeTab === 'ongoing') {
            $query->whereIn('status', ['pending', 'processing', 'shipped']);
        } elseif ($this->activeTab === 'completed') {
            $query->whereIn('status', ['completed', 'delivered']);
        } elseif ($this->activeTab === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $orders = $query->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('livewire.storefront.dashboard.orders', compact('orders'))
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
