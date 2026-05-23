<?php

namespace App\Livewire\Storefront;

use App\Models\BankAccount;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadReceipt extends Component
{
    use WithFileUploads;

    public Order $order;
    public $receiptImage;
    public $bankAccounts;
    public $isProcessing = false;

    public function mount(Order $order)
    {
        $this->order = $order->load('payment');
        $this->bankAccounts = BankAccount::where('is_active', true)->orderBy('sort')->get();
    }

    public function upload()
    {
        $this->validate([
            'receiptImage' => ['required', 'image', 'max:2048'], // 2MB Max
        ]);

        $this->isProcessing = true;

        if ($this->receiptImage) {
            $payment = $this->order->payment;
            
            // Delete old proof if it was rejected
            if ($payment->proof_image && Storage::disk('public')->exists($payment->proof_image)) {
                Storage::disk('public')->delete($payment->proof_image);
            }
            
            $path = $this->receiptImage->store('payment-proofs', 'public');
            
            // Update payment record
            $payment->update([
                'proof_image' => $path,
                'status' => 'pending', // Reset to pending if it was rejected
                'rejection_reason' => null, // Clear previous rejection reason
            ]);

            // Reset upload field
            $this->receiptImage = null;
            
            session()->flash('receipt-uploaded', __('Bukti transfer berhasil diunggah! Mohon tunggu verifikasi admin.'));
            
            // Refresh order
            $this->order->refresh();
        }

        $this->isProcessing = false;
    }

    public function render()
    {
        return view('livewire.storefront.upload-receipt');
    }
}
