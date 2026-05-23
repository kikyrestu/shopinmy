<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Admin Portal';
    }

    public function getSubheading(): ?string
    {
        return config('app.name', 'CommBuildy') . ' System';
    }
}
