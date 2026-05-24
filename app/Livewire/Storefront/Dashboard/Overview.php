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

        $pendingCount = Order::where('user_id', $user->id)->where('status', 'pending')->count();
        $processingCount = Order::where('user_id', $user->id)->where('status', 'processing')->count();
        $shippedCount = Order::where('user_id', $user->id)->where('status', 'shipped')->count();
        $completedCount = Order::where('user_id', $user->id)->where('status', 'completed')->where('created_at', '>=', now()->subDays(7))->count();

        return view('livewire.storefront.dashboard.overview', compact('totalOrders', 'totalSpent', 'recentOrders', 'pendingCount', 'processingCount', 'shippedCount', 'completedCount'))
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
