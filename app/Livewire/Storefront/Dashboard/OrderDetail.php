<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class OrderDetail extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Authorization
        if ($order->user_id !== auth()->id() && $order->guest_email !== auth()->user()->email) {
            abort(403);
        }

        $this->order = Order::with(['items.product.primaryImage', 'items.variant', 'payment', 'reviews'])
            ->where(function($q) {
                $q->where('user_id', auth()->id())
                    ->orWhere('guest_email', auth()->user()?->email);
            })
            ->findOrFail($order->id);
    }

    public function cancelOrder()
    {
        if ($this->order->status !== 'pending') {
            session()->flash('error', __('Only pending orders can be cancelled.'));
            return;
        }

        try {
            DB::transaction(function () {
                $order = Order::lockForUpdate()->find($this->order->id);
                
                if ($order->status !== 'pending') {
                    throw new \Exception(__('Order status has changed.'));
                }

                // Update order status
                $order->update(['status' => 'cancelled']);

                // Update payment status
                if ($order->payment) {
                    $order->payment->update(['status' => 'failed']);
                }

                // Restore stock if payment method is manual_transfer or cod (because they decremented on checkout)
                if ($order->payment && in_array($order->payment->method, ['manual_transfer', 'cod'])) {
                    foreach ($order->items as $item) {
                        if ($item->product->stock !== null) {
                            $item->product->increment('stock', $item->qty);
                        }
                        if ($item->variant && $item->variant->stock !== null) {
                            $item->variant->increment('stock', $item->qty);
                        }
                    }
                }

                // Restore voucher limit
                if ($order->voucher_id) {
                    $voucher = Voucher::lockForUpdate()->find($order->voucher_id);
                    if ($voucher && $voucher->used_count > 0) {
                        $voucher->decrement('used_count');
                    }
                }
            });

            session()->flash('success', __('Order has been cancelled successfully.'));
            $this->order->refresh();

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function completeOrder()
    {
        if (!in_array($this->order->status, ['shipped', 'delivered'])) {
            session()->flash('error', __('Only shipped orders can be completed.'));
            return;
        }

        try {
            DB::transaction(function () {
                $order = Order::lockForUpdate()->find($this->order->id);
                
                if (!in_array($order->status, ['shipped', 'delivered'])) {
                    throw new \Exception(__('Order status has changed.'));
                }

                $order->update(['status' => 'completed']);
            });

            session()->flash('success', __('Pesanan berhasil diselesaikan. Terima kasih telah berbelanja!'));
            $this->order->refresh();

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.storefront.dashboard.order-detail')
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
