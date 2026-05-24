<?php

namespace App\Livewire\Storefront\Dashboard;

use App\Models\Order;
use App\Models\Review;
use Livewire\Component;

class OrderReview extends Component
{
    public $order;
    public $courierRating = 5;
    public $productRatings = [];

    public function mount(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($order->status, ['completed', 'delivered'])) {
            abort(403, 'Order must be completed to be reviewed.');
        }

        if ($order->reviews()->exists()) {
            session()->flash('success', 'Pesanan ini sudah diulas.');
            return redirect()->route('dashboard.orders');
        }

        $this->order = $order->load('items.product.primaryImage');

        foreach ($order->items as $item) {
            $this->productRatings[$item->product_id] = [
                'rating' => 5,
                'comment' => '',
            ];
        }
    }

    public function setCourierRating($rating)
    {
        $this->courierRating = $rating;
    }

    public function setProductRating($productId, $rating)
    {
        $this->productRatings[$productId]['rating'] = $rating;
    }

    public function submitReview()
    {
        $this->validate([
            'courierRating' => 'required|integer|min:1|max:5',
            'productRatings.*.rating' => 'required|integer|min:1|max:5',
            'productRatings.*.comment' => 'nullable|string|max:1000',
        ]);

        // Save courier rating
        $this->order->update([
            'courier_rating' => $this->courierRating
        ]);

        // Save product reviews
        foreach ($this->productRatings as $productId => $data) {
            Review::create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'order_id' => $this->order->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);
        }

        session()->flash('success', 'Terima kasih! Ulasan berhasil dikirim.');
        return redirect()->route('dashboard.orders');
    }

    public function render()
    {
        return view('livewire.storefront.dashboard.order-review')
            ->extends('layouts.storefront')
            ->section('content');
    }
}
