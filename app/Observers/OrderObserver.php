<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // Add Loyalty Points when an order is completed
        if ($order->isDirty('status') && $order->status === 'completed') {
            if (\App\Models\Setting::get('loyalty_enabled', true)) {
                $pointsPerRm = (float) \App\Models\Setting::get('loyalty_points_per_rm', 1);
                $pointsEarned = floor($order->total * $pointsPerRm);
                
                if ($pointsEarned > 0) {
                    \App\Models\LoyaltyPoint::firstOrCreate(
                        ['type' => 'purchase', 'ref_id' => $order->id],
                        [
                            'user_id' => $order->user_id,
                            'points' => $pointsEarned,
                            'description' => 'Points earned from Order #' . $order->order_number,
                        ]
                    );
                }
            }
        }

        // Restore stock when an order is cancelled
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            // Only restore if the order was previously paid/processing
            $originalStatus = $order->getOriginal('status');
            if (in_array($originalStatus, ['processing', 'shipped', 'completed'])) {
                foreach ($order->items as $item) {
                    if ($item->product->stock !== null) {
                        $item->product->increment('stock', $item->qty);
                    }
                    if ($item->variant && $item->variant->stock !== null) {
                        $item->variant->increment('stock', $item->qty);
                    }
                }
            }
        }
    }
}
