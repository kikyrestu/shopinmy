<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Order::with(['user', 'payment'])->latest()->get();
    }

    public function headings(): array
    {
        return ['ID', 'Customer', 'Email', 'Total (RM)', 'Status', 'Kurir', 'Tracking No', 'Tanggal'];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->user?->name ?? $order->guest_name ?? '-',
            $order->user?->email ?? $order->guest_email ?? '-',
            number_format($order->total, 2),
            $order->status,
            $order->courier ?? '-',
            $order->order_number ?? '-',
            $order->created_at->format('d/m/Y H:i'),
        ];
    }
}
