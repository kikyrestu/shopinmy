<?php

namespace App\Livewire\SecureAdmin;

use Livewire\Component;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentVerification extends Component
{
    public $rejectionReason = '';
    public $selectedPaymentId = null;

    public function mount()
    {
        // Security check: Only Admins
        $user = auth()->user();
        
        $hasAdminAccess = $user && (
            $user->hasRole('Super Admin') || 
            $user->hasRole('super_admin') || 
            $user->hasRole('Admin') || 
            $user->hasRole('admin') ||
            $user->id === 1 // Fallback for root user
        );

        abort_unless($hasAdminAccess, 403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    public function approve($paymentId)
    {
        $payment = Payment::with('order')->findOrFail($paymentId);

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'paid',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'rejection_reason' => null,
            ]);

            $payment->order->update([
                'status' => 'paid',
            ]);
        });

        $this->dispatch('notify', message: 'Pembayaran berhasil disetujui!', type: 'success');
    }

    public function openRejectModal($paymentId)
    {
        $this->selectedPaymentId = $paymentId;
        $this->rejectionReason = '';
        $this->dispatch('open-modal', 'reject-payment-modal');
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|min:5|max:255',
        ], [
            'rejectionReason.required' => 'Alasan penolakan wajib diisi.',
            'rejectionReason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $payment = Payment::findOrFail($this->selectedPaymentId);

        $payment->update([
            'status' => 'failed',
            'rejection_reason' => $this->rejectionReason,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        $this->dispatch('close-modal', 'reject-payment-modal');
        $this->dispatch('notify', message: 'Pembayaran berhasil ditolak.', type: 'success');
    }

    public function render()
    {
        $payments = Payment::with(['order.user'])
            ->where('method', 'manual_transfer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.secure-admin.payment-verification', [
            'payments' => $payments
        ])->layout('layouts.secure-admin'); // Bypass Vite
    }
}
