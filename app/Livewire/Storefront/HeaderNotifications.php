<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class HeaderNotifications extends Component
{
    public function getUnreadCountProperty()
    {
        return Auth::check() ? Auth::user()->unreadNotifications->count() : 0;
    }

    public function getNotificationsProperty()
    {
        return Auth::check() ? Auth::user()->notifications()->take(5)->get() : collect();
    }

    public function markAsRead($notificationId)
    {
        if (Auth::check()) {
            $notification = Auth::user()->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
                
                // Redirect if there's a url in data
                if (isset($notification->data['url'])) {
                    return redirect($notification->data['url']);
                }
            }
        }
    }

    public function markAllAsRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
    }

    public function render()
    {
        return view('livewire.storefront.header-notifications');
    }
}
