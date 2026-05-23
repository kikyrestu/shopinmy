<?php

namespace App\Livewire\Storefront;

use App\Models\NewsletterSubscriber;
use Livewire\Component;

class NewsletterForm extends Component
{
    public $email;
    public $successMessage = false;

    protected $rules = [
        'email' => 'required|email|unique:newsletter_subscribers,email',
    ];

    protected $messages = [
        'email.unique' => 'This email is already subscribed!',
    ];

    public function subscribe()
    {
        $this->validate();

        NewsletterSubscriber::create([
            'email' => $this->email,
            'subscribed_at' => now(),
        ]);

        $this->email = '';
        $this->successMessage = true;

        // Hide success message after 3 seconds using browser dispatch
        $this->dispatch('notify', message: __('Thank you for subscribing!'));
    }

    public function render()
    {
        return view('livewire.storefront.newsletter-form');
    }
}
