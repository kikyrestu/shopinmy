<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #f59e0b; padding-bottom: 15px; }
        .logo { font-size: 22px; font-weight: bold; color: #f59e0b; }
        .invoice-title { font-size: 28px; font-weight: bold; text-align: right; color: #1f2937; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-label { font-weight: bold; color: #6b7280; width: 140px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #f59e0b; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        .items-table tr:nth-child(even) { background: #fefce8; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals td { padding: 5px 10px; }
        .totals .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #1f2937; color: #1f2937; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <table style="width:100%; margin-bottom: 30px;">
        <tr>
            <td><div class="logo">{{ setting('site_name') ?? 'CommBuildy' }}</div></td>
            <td class="text-right"><div class="invoice-title">INVOICE</div></td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width:50%">
                <table>
                    <tr><td class="info-label">Invoice No</td><td>: INV-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                    <tr><td class="info-label">Order No</td><td>: {{ $order->order_number ?? '-' }}</td></tr>
                    <tr><td class="info-label">Date</td><td>: {{ $order->created_at->format('d/m/Y') }}</td></tr>
                    <tr><td class="info-label">Status</td><td>: {{ ucfirst($order->status) }}</td></tr>
                    @if($order->tracking_no)
                    <tr><td class="info-label">Tracking No</td><td>: {{ $order->tracking_no }}</td></tr>
                    @endif
                </table>
            </td>
            <td style="width:50%">
                <table>
                    <tr><td class="info-label">Customer</td><td>: {{ $order->user?->name ?? $order->guest_name ?? '-' }}</td></tr>
                    <tr><td class="info-label">Email</td><td>: {{ $order->user?->email ?? $order->guest_email ?? '-' }}</td></tr>
                    <tr><td class="info-label">Phone</td><td>: {{ $order->user?->phone ?? $order->guest_phone ?? '-' }}</td></tr>
                    @if($order->address)
                    <tr><td class="info-label">Address</td><td>: {{ $order->address->address }}, {{ $order->address->city }} {{ $order->address->postcode }}, {{ $order->address->state }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Variant</th>
                <th class="text-right">Price</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product?->name ?? 'Deleted Product' }}</td>
                <td>{{ $item->variant?->name ?? '-' }}</td>
                <td class="text-right">RM {{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">RM {{ number_format($item->price * $item->qty, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="info-label">Subtotal</td>
            <td class="text-right">RM {{ number_format($order->items->sum(fn($i) => $i->price * $i->qty), 2) }}</td>
        </tr>
        <tr>
            <td class="info-label">Shipping ({{ $order->courier ?? '-' }})</td>
            <td class="text-right">RM {{ number_format($order->shipping_cost, 2) }}</td>
        </tr>
        @if($order->tax_amount > 0)
        <tr>
            <td class="info-label">SST ({{ $order->tax_rate }}%)</td>
            <td class="text-right">RM {{ number_format($order->tax_amount, 2) }}</td>
        </tr>
        @endif
        @if($order->voucher)
        <tr>
            <td class="info-label">Voucher ({{ $order->voucher->code }})</td>
            <td class="text-right" style="color: #dc2626;">- RM {{ number_format($order->voucher->value, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>TOTAL</td>
            <td class="text-right">RM {{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    @if($order->payment)
    <div style="margin-top: 20px; padding: 10px; background: #f0fdf4; border-radius: 5px;">
        <strong>Payment:</strong> {{ ucfirst($order->payment->method) }} - {{ ucfirst($order->payment->status) }}
        @if($order->payment->reference) | Ref: {{ $order->payment->reference }} @endif
    </div>
    @endif

    <div class="footer">
        <p>Thank you for shopping with {{ setting('site_name') ?? 'CommBuildy' }}!</p>
        <p>{{ setting('site_email') ?? '' }} | {{ setting('site_phone') ?? '' }}</p>
    </div>
</body>
</html>
