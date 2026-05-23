<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;
use App\Models\Order;
use App\Services\MyParcelService;
use Illuminate\Support\Facades\Log;

class TrackOrder extends Component
{
    public Order $order;
    public $trackingData = [];
    public $isLoading = true;
    public $error = null;

    public function mount(Order $order)
    {
        $this->order = $order;

        if (auth()->check()) {
            if ($order->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            // If guest, verify session
            if (session('last_order_id') !== $order->id) {
                abort(403);
            }
        }

        $this->trackingData = [];
        $this->fetchTracking();
    }

    public function fetchTracking()
    {
        $this->isLoading = true;
        
        if (empty($this->order->tracking_no) || str_starts_with($this->order->tracking_no, 'ORD-')) {
            $this->error = 'Resi belum tersedia.';
            $this->isLoading = false;
            return;
        }

        try {
            $myParcel = app(\App\Services\MyParcelService::class);
            $data = $myParcel->trace($this->order->tracking_no);
            
            if (!empty($data[0]['tracker'])) {
                $this->trackingData = $data[0]['tracker'];
                $this->error = null;
            } elseif (!empty($data['tracker'])) {
                $this->trackingData = $data['tracker'];
                $this->error = null;
            } else {
                $this->error = 'waiting_update'; // Status code, not direct text
            }
        } catch (\Exception $e) {
            $this->error = 'waiting_update';
        }

        $this->isLoading = false;
    }

    public function refreshTracking()
    {
        $this->fetchTracking();
    }

    public function render()
    {
        return view('livewire.storefront.dashboard.track-order')
            ->extends('layouts.storefront')
            ->section('content');
    }
}
