<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification
{
    use Queueable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->data['title'] ?? 'Notification Title',
            'message' => $this->data['message'] ?? 'This is a notification message.',
            'url' => $this->data['url'] ?? route('dashboard'),
            'icon' => $this->data['icon'] ?? 'ph-bell-ringing',
        ];
    }
}
