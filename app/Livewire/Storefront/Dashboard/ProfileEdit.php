<?php

namespace App\Livewire\Storefront\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileEdit extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $avatar;
    
    // For manual user password change
    public $current_password;
    public $password;
    public $password_confirmation;

    public $isGoogleUser = false;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        
        if (!empty($user->google_id)) {
            $this->isGoogleUser = true;
        }
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ];

        if (!$this->isGoogleUser) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id];
        }

        $this->validate($rules);

        // Handle Avatar Upload
        if ($this->avatar) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $this->name;
        $user->phone = $this->phone;
        
        if (!$this->isGoogleUser) {
            if ($user->email !== $this->email) {
                $user->email = $this->email;
                $user->email_verified_at = null; // Require re-verification if email changes
            }
        }

        $user->save();

        // Reset temporary avatar
        $this->avatar = null;

        session()->flash('profile-updated', __('Profile updated successfully.'));
    }

    public function updatePassword()
    {
        if ($this->isGoogleUser) {
            return;
        }

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password-updated', __('Password updated successfully.'));
    }

    public function render()
    {
        return view('livewire.storefront.dashboard.profile-edit')
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
