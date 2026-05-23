<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;
use App\Models\Order;

class Overview extends Component
{
    public function render()
    {
        $user = auth()->user();
        
        $totalOrders = Order::where('user_id', $user->id)->count();
        $totalSpent = Order::where('user_id', $user->id)->where('status', '!=', 'cancelled')->sum('total');
        
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->latest()
            ->take(3)
            ->get();

        return view('livewire.storefront.dashboard.overview', compact('totalOrders', 'totalSpent', 'recentOrders'))
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
