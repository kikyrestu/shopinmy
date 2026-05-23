<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::withCount('orders')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Phone', 'Total Orders', 'Registered'];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '-',
            $user->orders_count,
            $user->created_at->format('d/m/Y'),
        ];
    }
}
