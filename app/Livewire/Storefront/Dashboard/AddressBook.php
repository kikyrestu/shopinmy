<?php

namespace App\Livewire\Storefront\Dashboard;

use Livewire\Component;
use App\Models\Address;

class AddressBook extends Component
{
    public $addresses;
    
    // Form fields
    public $isEditing = false;
    public $addressId = null;
    public $label;
    public $address;
    public $city;
    public $state;
    public $postcode;
    public $is_default = false;

    public function mount()
    {
        $this->loadAddresses();
    }

    public function loadAddresses()
    {
        $this->addresses = Address::where('user_id', auth()->id())->latest()->get();
    }

    public function resetForm()
    {
        $this->reset(['isEditing', 'addressId', 'label', 'address', 'city', 'state', 'postcode', 'is_default']);
        $this->resetValidation();
    }

    public function editAddress($id)
    {
        $address = Address::where('user_id', auth()->id())->findOrFail($id);
        
        $this->addressId = $address->id;
        $this->label = $address->label;
        $this->address = $address->address;
        $this->city = $address->city;
        $this->state = $address->state;
        $this->postcode = $address->postcode;
        $this->is_default = (bool) $address->is_default;
        
        $this->isEditing = true;
    }

    public function saveAddress()
    {
        $this->validate([
            'label' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        // If setting as default, remove default from others
        if ($this->is_default) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        // If it's the first address, force it to be default
        if ($this->addresses->isEmpty()) {
            $this->is_default = true;
        }

        if ($this->addressId) {
            $address = Address::where('user_id', auth()->id())->findOrFail($this->addressId);
            $address->update([
                'label' => $this->label,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'postcode' => $this->postcode,
                'is_default' => $this->is_default,
            ]);
            $this->dispatch('notify', message: __('Address updated successfully!'));
        } else {
            Address::create([
                'user_id' => auth()->id(),
                'label' => $this->label,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'postcode' => $this->postcode,
                'is_default' => $this->is_default,
            ]);
            $this->dispatch('notify', message: __('Address added successfully!'));
        }

        $this->resetForm();
        $this->loadAddresses();
    }

    public function deleteAddress($id)
    {
        $address = Address::where('user_id', auth()->id())->findOrFail($id);
        $address->delete();
        
        // If we deleted the default, make the newest one default if exists
        if ($address->is_default) {
            $newDefault = Address::where('user_id', auth()->id())->latest()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $this->loadAddresses();
        $this->dispatch('notify', message: __('Address removed successfully!'));
    }

    public function render()
    {
        return view('livewire.storefront.dashboard.addresses')
            ->extends('components.dashboard-layout')
            ->section('dashboard_content');
    }
}
