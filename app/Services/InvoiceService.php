<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate PDF invoice for an order.
     */
    public static function generate(Order $order)
    {
        $order->load(['user', 'address', 'items.product', 'items.variant', 'payment', 'voucher']);

        return Pdf::loadView('invoices.order', compact('order'))
            ->setPaper('a4')
            ->setOption('defaultFont', 'Helvetica');
    }

    /**
     * Download PDF invoice.
     */
    public static function download(Order $order)
    {
        $filename = 'invoice_INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return response()->streamDownload(function () use ($order) {
            echo static::generate($order)->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Stream PDF invoice inline.
     */
    public static function stream(Order $order)
    {
        $filename = 'invoice_INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return response()->stream(function () use ($order) {
            echo static::generate($order)->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
