<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 40px 0; color: #374151; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #10b981; padding: 30px 40px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px; }
        .greeting { font-size: 18px; font-weight: 600; margin-bottom: 20px; }
        .order-meta { background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 30px; font-size: 14px; }
        .order-meta table { width: 100%; }
        .order-meta td { padding: 4px 0; }
        .order-meta .label { color: #6b7280; font-weight: 500; width: 40%; }
        .order-meta .val { font-weight: 700; color: #111827; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { text-align: left; padding: 12px 0; border-bottom: 2px solid #e5e7eb; color: #6b7280; font-size: 13px; text-transform: uppercase; }
        .items-table td { padding: 16px 0; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .item-name { font-weight: 600; color: #111827; margin: 0 0 4px 0; font-size: 15px; }
        .item-meta { font-size: 13px; color: #6b7280; margin: 0; }
        
        .totals { width: 100%; margin-bottom: 30px; }
        .totals td { padding: 8px 0; text-align: right; }
        .totals .label { color: #6b7280; width: 70%; }
        .totals .val { font-weight: 600; color: #111827; }
        .totals .grand-total td { border-top: 2px solid #e5e7eb; padding-top: 16px; margin-top: 8px; font-size: 18px; font-weight: 800; color: #10b981; }
        
        .button-wrap { text-align: center; margin-top: 40px; }
        .button { display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 600; }
        
        .footer { text-align: center; padding: 30px; color: #9ca3af; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ \App\Models\Setting::get('site_name', 'ShopinMy') }}</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hello {{ $order->user ? $order->user->name : $order->guest_name }},
            </div>
            <p style="margin-bottom: 30px; line-height: 1.6;">
                Thank you for your purchase! We've received your order and we're getting it ready to be shipped.
            </p>
            
            <div class="order-meta">
                <table>
                    <tr>
                        <td class="label">Order Number</td>
                        <td class="val">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Order Date</td>
                        <td class="val">{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td class="val"><span style="color: #f59e0b;">{{ strtoupper($order->status) }}</span></td>
                    </tr>
                </table>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align: right; width: 80px;">Qty</th>
                        <th style="text-align: right; width: 100px;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <p class="item-name">{{ $item->product->name }}</p>
                            @if($item->variant)
                                <p class="item-meta">{{ $item->variant->name }}</p>
                            @endif
                        </td>
                        <td style="text-align: right;">{{ $item->qty }}</td>
                        <td style="text-align: right;">RM {{ number_format($item->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <table class="totals">
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="val">RM {{ number_format($order->total - $order->shipping_cost, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Shipping</td>
                    <td class="val">RM {{ number_format($order->shipping_cost, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="label">Total</td>
                    <td class="val">RM {{ number_format($order->total, 2) }}</td>
                </tr>
            </table>
            
            <div class="button-wrap">
                <a href="{{ url('/') }}" class="button">Continue Shopping</a>
            </div>
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'ShopinMy') }}. All rights reserved.<br>
        If you have any questions, please contact us at {{ \App\Models\Setting::get('site_email', 'support@example.com') }}.
    </div>
</body>
</html>
