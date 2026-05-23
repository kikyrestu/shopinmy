<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Payment::with(['order'])->latest()->get();
    }

    public function headings(): array
    {
        return ['ID', 'Order ID', 'Type', 'Method', 'Amount (RM)', 'Status', 'Verified At', 'Tanggal'];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->order_id,
            $payment->type,
            $payment->method,
            number_format($payment->amount, 2),
            $payment->status,
            $payment->verified_at?->format('d/m/Y H:i') ?? '-',
            $payment->created_at->format('d/m/Y H:i'),
        ];
    }
}
