<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;

class LoyaltyView extends Component
{
    public function mount()
    {
        $loyaltyOn = \App\Models\Setting::get('loyalty_enabled', true);
        $referralOn = \App\Models\Setting::get('referral_enabled', true);

        if (!$loyaltyOn && !$referralOn) {
            return $this->redirect(route('dashboard.orders'), navigate: true);
        }
    }

    public function render()
    {
        $user = auth()->user();
        
        // Load loyalty points and referrals
        $user->loadMissing(['loyaltyPoints' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'referralsMade.referee']);

        $totalPoints = $user->total_points;
        $referralLink = route('register', ['ref' => $user->referral_code]);
        $referralEnabled = \App\Models\Setting::get('referral_enabled', true);

        return view('livewire.storefront.dashboard.loyalty-view', compact('user', 'totalPoints', 'referralLink', 'referralEnabled'))
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
