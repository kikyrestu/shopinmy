<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $webhookSecret = Setting::get('stripe_webhook_secret');
        if (empty($webhookSecret)) {
            Log::warning('Stripe webhook secret is not configured.');
            return response()->json(['error' => 'Webhook secret not configured'], 400);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error handling webhook'], 400);
        }
        
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $order = Order::find($session->client_reference_id);
            
            if ($order && $order->payment && $order->payment->status !== 'paid') {
                $order->payment->update(['status' => 'paid']);
                $order->update(['status' => 'processing']);
                
                // Decrement stock ONLY upon payment confirmation (Bug-08)
                foreach ($order->items as $item) {
                    $item->product->decrement('stock', $item->qty);
                    if ($item->variant) {
                        $item->variant->decrement('stock', $item->qty);
                    }
                }
            }
        }
        
        return response()->json(['status' => 'ok']);
    }
}
