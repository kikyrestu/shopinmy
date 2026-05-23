<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function process(Order $order)
    {
        // Authorization checks
        if (auth()->check() && $order->user_id !== auth()->id()) abort(403);
        if (!auth()->check() && session('last_order_id') !== $order->id) abort(403);

        $method = $order->payment->method ?? '';

        if ($method === 'billplz') {
            return $this->processBillplz($order);
        }

        if ($method === 'stripe') {
            return $this->processStripe($order);
        }

        return redirect()->route('checkout.success', $order->id);
    }

    private function processBillplz(Order $order)
    {
        $apiKey = Setting::get('billplz_api_key');
        $collectionId = Setting::get('billplz_collection_id');
        $isSandbox = Setting::get('billplz_sandbox');
        
        if (empty($apiKey) || empty($collectionId)) {
            return back()->withError('Billplz is not configured properly.');
        }

        $baseUrl = $isSandbox ? 'https://www.billplz-sandbox.com/api/v3' : 'https://www.billplz.com/api/v3';
        
        $customerName = $order->user ? $order->user->name : $order->guest_name;
        $customerEmail = $order->user ? $order->user->email : $order->guest_email;
        $customerPhone = $order->user ? $order->user->phone : $order->guest_phone;

        $response = Http::withBasicAuth($apiKey, '')
            ->post($baseUrl . '/bills', [
                'collection_id' => $collectionId,
                'description' => 'Payment for Order ' . $order->order_number,
                'email' => $customerEmail,
                'name' => $customerName,
                'amount' => round($order->total * 100), // Billplz amount is in cents
                'reference_1_label' => 'Order ID',
                'reference_1' => $order->id,
                'redirect_url' => route('payment.callback.billplz', ['order' => $order->id]),
            ]);

        if ($response->successful()) {
            $bill = $response->json();
            
            // Save bill id to payment
            if ($order->payment) {
                $order->payment->update(['transaction_id' => $bill['id']]);
            }
            
            return redirect($bill['url']);
        }

        \Illuminate\Support\Facades\Log::error('Billplz Error: ' . $response->body());
        return redirect()->route('checkout.index')->withErrors(['cart' => 'Payment Gateway Error: ' . $response->json('error.message', 'Failed to create billplz bill.')]);
    }

    private function processStripe(Order $order)
    {
        $secretKey = Setting::get('stripe_secret_key');
        
        if (empty($secretKey)) {
            return back()->withError('Stripe is not configured properly.');
        }

        \Stripe\Stripe::setApiKey($secretKey);

        $customerEmail = $order->user ? $order->user->email : $order->guest_email;

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $customerEmail,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'myr',
                        'product_data' => [
                            'name' => 'Order ' . $order->order_number,
                        ],
                        'unit_amount' => round($order->total * 100), // Stripe amount is in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.callback.stripe', ['order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.index'),
                'client_reference_id' => $order->id,
            ]);
            
            // Save session id to payment
            if ($order->payment) {
                $order->payment->update(['transaction_id' => $session->id]);
            }

            return redirect($session->url);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe Error: ' . $e->getMessage());
            return redirect()->route('checkout.index')->withErrors(['cart' => 'Payment Gateway Error: ' . $e->getMessage()]);
        }
    }

    public function billplzCallback(Request $request)
    {
        $orderId = $request->order;
        $order = Order::findOrFail($orderId);
        
        // Authorization
        if (auth()->check() && $order->user_id !== auth()->id()) abort(403);
        if (!auth()->check() && session('last_order_id') !== $order->id) abort(403);

        $data = $request->billplz ?? [];
        $billplzId = $data['id'] ?? null;
        
        // Verify X-Signature
        $xSignatureKey = Setting::get('billplz_x_signature');
        if ($xSignatureKey && $billplzId) {
            $expectedSig = hash_hmac('sha256', 
                ($data['id'] ?? '') . '|' . ($data['collection_id'] ?? '') . '|' .
                ($data['paid'] ?? '') . '|' . ($data['state'] ?? ''),
                $xSignatureKey
            );
            if (!hash_equals($expectedSig, $data['x_signature'] ?? '')) {
                abort(400, 'Invalid signature');
            }
        }

        if ($billplzId) {
            // Verify bill status via API
            $apiKey = Setting::get('billplz_api_key');
            $isSandbox = Setting::get('billplz_sandbox');
            $baseUrl = $isSandbox ? 'https://www.billplz-sandbox.com/api/v3' : 'https://www.billplz.com/api/v3';

            $response = Http::withBasicAuth($apiKey, '')->get($baseUrl . '/bills/' . $billplzId);
            
            if ($response->successful()) {
                $billData = $response->json();
                if ($billData['state'] === 'paid') {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                        $payment = \App\Models\Payment::lockForUpdate()->find($order->payment->id);
                        if ($payment->status !== 'paid') {
                            $payment->update(['status' => 'paid']);
                            $order->update(['status' => 'processing']);
                            
                            // Decrement stock ONLY upon payment confirmation (Bug-08, Bug-C02, Bug-C03)
                            foreach ($order->items as $item) {
                                if ($item->product->stock !== null) {
                                    $item->product->decrement('stock', $item->qty);
                                }
                                if ($item->variant && $item->variant->stock !== null) {
                                    $item->variant->decrement('stock', $item->qty);
                                }
                            }
                        }
                    });
                } else {
                    $order->payment->update(['status' => 'failed']);
                }
            }
        }

        return redirect()->route('checkout.success', $order->id);
    }

    public function stripeCallback(Request $request)
    {
        $orderId = $request->order;
        $order = Order::findOrFail($orderId);
        
        // Authorization
        if (auth()->check() && $order->user_id !== auth()->id()) abort(403);
        if (!auth()->check() && session('last_order_id') !== $order->id) abort(403);

        $sessionId = $request->session_id;

        if ($sessionId) {
            $secretKey = Setting::get('stripe_secret_key');
            \Stripe\Stripe::setApiKey($secretKey);

            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                if ($session->payment_status === 'paid') {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                        $payment = \App\Models\Payment::lockForUpdate()->find($order->payment->id);
                        if ($payment->status !== 'paid') {
                            $payment->update(['status' => 'paid']);
                            $order->update(['status' => 'processing']);
                            
                            // Decrement stock ONLY upon payment confirmation (Bug-08, Bug-C02, Bug-C03)
                            foreach ($order->items as $item) {
                                if ($item->product->stock !== null) {
                                    $item->product->decrement('stock', $item->qty);
                                }
                                if ($item->variant && $item->variant->stock !== null) {
                                    $item->variant->decrement('stock', $item->qty);
                                }
                            }
                        }
                    });
                } else {
                    $order->payment->update(['status' => 'failed']);
                }
            } catch (\Exception $e) {
                // Ignore retrieval errors for callback display
                \Illuminate\Support\Facades\Log::error('Stripe Callback Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('checkout.success', $order->id);
    }

    public function billplzWebhook(Request $request)
    {
        $data = $request->all();
        $xSignatureKey = Setting::get('billplz_x_signature');
        
        if (empty($xSignatureKey)) {
            return response()->json(['error' => 'X-Signature key not configured'], 400); // Bug-C08
        }
        
        if (!empty($data['id'])) {
            $expectedSig = hash_hmac('sha256', 
                ($data['id'] ?? '') . '|' . ($data['collection_id'] ?? '') . '|' .
                ($data['paid'] ?? '') . '|' . ($data['state'] ?? ''),
                $xSignatureKey
            );
            if (!hash_equals($expectedSig, $data['x_signature'] ?? '')) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        // Find order by transaction_id OR reference_1
        $payment = \App\Models\Payment::where('transaction_id', $data['id'])->first();
        if (!$payment) {
            $payment = \App\Models\Payment::where('order_id', $data['reference_1'] ?? null)->first();
        }

        if ($payment && $payment->order) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($payment, $data) {
                $lockedPayment = \App\Models\Payment::lockForUpdate()->find($payment->id);
                if ($lockedPayment->status !== 'paid') {
                    if (($data['state'] ?? '') === 'paid') {
                        $lockedPayment->update(['status' => 'paid']);
                        $lockedPayment->order->update(['status' => 'processing']);
                        
                        // Decrement stock ONLY upon payment confirmation (Bug-08, Bug-C02, Bug-C03)
                        foreach ($lockedPayment->order->items as $item) {
                            if ($item->product->stock !== null) {
                                $item->product->decrement('stock', $item->qty);
                            }
                            if ($item->variant && $item->variant->stock !== null) {
                                $item->variant->decrement('stock', $item->qty);
                            }
                        }
                    } else {
                        $lockedPayment->update(['status' => 'failed']);
                    }
                }
            });
        }

        return response()->json(['status' => 'ok']);
    }
}
